<?php

namespace App\Services\DocuSeal;

use App\Mail\OfferAcceptanceUpdate;
use App\Mail\OfferDeclinedNotice;
use App\Models\AdmissionNotification;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantDocusealSubmission as Row;
use App\Models\ApplicantFeeEligibility;
use App\Models\FeeEligibility;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentConversionLog;
use App\Services\Admission\StudentConversion;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * What happens when the applicant acts on the DocuSeal offer acceptance form
 * (webhook → ProcessDocusealSubmission job, or the 10-minute backstop command).
 * DocuSeal's API is asked for the submission each time; the webhook body is
 * never trusted for the status.
 *
 * Signed (submission completed):
 *   1. the signed form + audit log are stored in the uploads (OfferAcceptanceDocuments);
 *   2. an applicant still at Offer Placed goes to Offer Accepted and the 21 conversion
 *      jobs are queued (StudentConversion), exactly as the Change status dialog does;
 *   3. admin is emailed when the conversion finishes (or stops).
 *   Proof of ID missing → nothing changes and admin is emailed to complete it and set
 *   Offer Accepted from Change status. No fee eligibility on record → the dialog's
 *   default, "Eligibility to pay home fees not assessed" (3), is saved.
 * Steps 2–3 (and the proof-of-ID email) only run while services.docuseal.auto_convert
 * (DOCUSEAL_AUTO_CONVERT) is on; otherwise a signed form is only stored.
 * Declined (on the DocuSeal form, or from the Decline button in the offer email — OfferDecline):
 * Offer Placed → Offer Rejected; admin (+ the staff member who sent the form) is emailed with the
 * reason, the applicant gets a confirmation and their agent, if any, a notice.
 * Any other current status is left alone (staff already moved the application), and only
 * the applicant's most recently sent form can change the status — an older link signed or
 * declined later (e.g. after the offer was re-sent) just has its documents stored.
 * Every admin email is also stored in admission_notifications (user_id = who sent the form).
 */
class OfferAcceptanceWorkflow
{
    public const OFFER_PLACED = 6;
    public const OFFER_ACCEPTED = 7;
    public const OFFER_REJECTED = 9;
    public const FEE_NOT_ASSESSED = 3;

    public function __construct(
        private OfferAcceptanceDocuments $documents,
        private StudentConversion $conversion,
    ) {
    }

    /** @return array{state:string,message:string} */
    public function process(Row $row, array $hints = []): array
    {
        $result = ['state' => $row->state, 'message' => 'Nothing to do.'];
        if (in_array($row->state, [Row::STATE_WAITING, Row::STATE_FAILED], true)) {
            $result = $this->documents->check($row);
            $row->refresh();
        }
        if (!empty($hints['decline_reason']) && empty($row->decline_reason)) {
            $row->decline_reason = mb_substr((string) $hints['decline_reason'], 0, 2000);
            $row->save();
        }
        if ($row->outcome) {
            return $result;
        }
        if ($row->state === Row::STATE_DECLINED) {
            return $this->decline($row);
        }
        if (in_array($row->state, [Row::STATE_STORED, Row::STATE_SKIPPED], true)) {
            return $this->accept($row);
        }

        return $result;
    }

    /** @return array{state:string,message:string} */
    public function accept(Row $row): array
    {
        // Switched off (DOCUSEAL_AUTO_CONVERT): the signed form is in the uploads, nothing
        // else changes. The row keeps no outcome, so it is picked up once this is turned on.
        if (!config('services.docuseal.auto_convert')) {
            return ['state' => 'paused', 'message' => 'Signed form stored. Offer Accepted + move to students is switched off (DOCUSEAL_AUTO_CONVERT).'];
        }

        $system = (int) config('tasks.system_user_id', 1);
        $mail = null;

        $result = DB::transaction(function () use ($row, $system, &$mail) {
            $locked = Row::whereKey($row->id)->lockForUpdate()->first();
            if (!$locked || $locked->outcome) {
                return ['state' => 'done', 'message' => 'Already handled.'];
            }
            $applicant = Applicant::with(['proof', 'feeeligibility', 'status'])->find($locked->applicant_id);
            if (!$applicant) {
                return $this->outcome($locked, Row::OUTCOME_UNCHANGED, 'Applicant #'.$locked->applicant_id.' no longer exists.');
            }
            if ((int) $applicant->status_id !== self::OFFER_PLACED) {
                return $this->outcome($locked, Row::OUTCOME_UNCHANGED, 'Signed form stored; status left as "'.($applicant->status->name ?? $applicant->status_id).'" because it is no longer Offer Placed.');
            }
            if ($newer = $this->newerForm($locked)) {
                return $this->outcome($locked, Row::OUTCOME_UNCHANGED, 'Signed, but a newer offer acceptance form ('.$newer->submission_id.') was sent '.optional($newer->sent_at)->format('d-m-Y H:i').', so this one does not change the status.');
            }

            $missing = $this->missingProof($applicant);
            if ($missing) {
                $mail = ['held', $applicant, ['missing' => $missing, 'signed_at' => $locked->completed_at], $locked];

                return $this->outcome($locked, Row::OUTCOME_HELD, 'Signed, but '.implode(', ', $missing).' '.(count($missing) > 1 ? 'are' : 'is').' missing, so the status stays Offer Placed.');
            }

            $feeNote = null;
            if (empty($applicant->feeeligibility->fee_eligibility_id)) {
                ApplicantFeeEligibility::create(['applicant_id' => $applicant->id, 'fee_eligibility_id' => self::FEE_NOT_ASSESSED, 'created_by' => $system, 'updated_by' => $system]);
                $feeNote = 'No fee eligibility was recorded, so "'.(FeeEligibility::find(self::FEE_NOT_ASSESSED)->name ?? 'not assessed').'" was saved, as the Change status dialog does by default.';
            }

            $this->setStatus($applicant, self::OFFER_ACCEPTED, $system);
            $locked->fill(['outcome' => Row::OUTCOME_CONVERTING, 'outcome_note' => $feeNote, 'outcome_at' => now()])->save();

            return ['state' => Row::OUTCOME_CONVERTING, 'message' => 'Offer Accepted; conversion to student queued.', 'applicant' => $applicant];
        });

        if ($mail) {
            $this->notify(...$mail);
        }
        if (($result['state'] ?? '') !== Row::OUTCOME_CONVERTING) {
            return $result;
        }

        // Queued after the commit so the jobs see status 7.
        $rowId = (int) $row->id;
        try {
            $batch = $this->conversion->start(
                $result['applicant']->fresh(['proof', 'feeeligibility']),
                $system,
                null,
                function (Batch $batch) use ($rowId) {
                    app(OfferAcceptanceWorkflow::class)->conversionFinished($rowId, $batch->id, true);
                },
                function (Batch $batch, Throwable $e) use ($rowId) {
                    app(OfferAcceptanceWorkflow::class)->conversionFinished($rowId, $batch->id, false, $e->getMessage());
                }
            );
            Row::whereKey($rowId)->update(['batch_id' => $batch->id]);
            Log::info('[DocuSeal] Offer accepted by signing; conversion queued for applicant #'.$row->applicant_id.'.', ['submission_id' => $row->submission_id, 'batch_id' => $batch->id]);
        } catch (Throwable $e) {
            $this->conversionFinished($rowId, null, false, 'The conversion could not be queued: '.$e->getMessage());
        }

        return ['state' => Row::OUTCOME_CONVERTING, 'message' => $result['message']];
    }

    /**
     * Offer Placed → Offer Rejected. $via is where the applicant declined: "docuseal" (the
     * Decline button on the DocuSeal form) or "email_link" (the Decline button in the offer
     * email, OfferDecline — $extra['withdrawn'] says whether the DocuSeal form was archived).
     * Admissions (and the staff member who sent the form), the applicant and their agent are emailed.
     *
     * @return array{state:string,message:string}
     */
    public function decline(Row $row, string $via = 'docuseal', array $extra = []): array
    {
        $system = (int) config('tasks.system_user_id', 1);
        $mail = null;

        $result = DB::transaction(function () use ($row, $system, $via, $extra, &$mail) {
            $locked = Row::whereKey($row->id)->lockForUpdate()->first();
            if (!$locked || $locked->outcome) {
                return ['state' => 'done', 'message' => 'Already handled.'];
            }
            $applicant = Applicant::with('status')->find($locked->applicant_id);
            if (!$applicant || (int) $applicant->status_id !== self::OFFER_PLACED) {
                return $this->outcome($locked, Row::OUTCOME_UNCHANGED, 'Declined; status left as "'.($applicant->status->name ?? '—').'" because it is no longer Offer Placed.');
            }
            if ($newer = $this->newerForm($locked)) {
                return $this->outcome($locked, Row::OUTCOME_UNCHANGED, 'Declined, but a newer offer acceptance form ('.$newer->submission_id.') was sent '.optional($newer->sent_at)->format('d-m-Y H:i').', so this one does not change the status.');
            }
            $this->setStatus($applicant, self::OFFER_REJECTED, $system);
            // $mail = ['rejected', $applicant, ['reason' => $locked->decline_reason, 'declined_at' => $locked->last_checked_at], $locked];
            $mail = ['rejected', $applicant, ['reason' => $locked->decline_reason, 'declined_at' => $locked->last_checked_at ?? now(), 'via' => $via] + $extra, $locked];

            // return $this->outcome($locked, Row::OUTCOME_REJECTED, 'Declined on DocuSeal; status set to Offer Rejected.');
            return $this->outcome($locked, Row::OUTCOME_REJECTED, $via === 'email_link'
                ? 'Declined from the Decline button in the offer email; status set to Offer Rejected.'.(array_key_exists('withdrawn', $extra) && !$extra['withdrawn'] ? ' The DocuSeal form could not be archived.' : '')
                : 'Declined on DocuSeal; status set to Offer Rejected.');
        });

        if ($mail) {
            $this->notify(...$mail);
            // the other parties (user, 27 Oct 2026): the applicant and, when there is one, their agent
            $this->notifyParties($mail[1], $mail[2], $mail[3]);
        }

        return $result;
    }

    /** Batch callback: the conversion queued by accept() finished or stopped. */
    public function conversionFinished(int $rowId, ?string $batchId, bool $ok, ?string $error = null): void
    {
        $row = Row::find($rowId);
        if (!$row || in_array($row->outcome, [Row::OUTCOME_CONVERTED, Row::OUTCOME_CONVERSION_FAILED], true)) {
            return;
        }
        $row->fill([
            'outcome' => $ok ? Row::OUTCOME_CONVERTED : Row::OUTCOME_CONVERSION_FAILED,
            'outcome_note' => trim(($row->outcome_note ? $row->outcome_note."\n" : '').($ok ? '' : mb_substr((string) $error, 0, 1500))) ?: null,
            'outcome_at' => now(),
            'batch_id' => $batchId ?: $row->batch_id,
        ])->save();

        $applicant = Applicant::find($row->applicant_id);
        if (!$applicant) {
            return;
        }
        $failed = $batchId ? StudentConversionLog::where('batch_id', $batchId)->where('status', StudentConversionLog::STATUS_FAILED)->first() : null;
        // The student this conversion created: from its log rows, else the applicant's latest student record.
        $studentId = $batchId ? StudentConversionLog::where('batch_id', $batchId)->max('student_id') : null;
        $student = $studentId ? Student::find($studentId) : null;
        if (!$student && $ok) {
            $student = Student::where('applicant_id', $applicant->id)->orderByDesc('id')->first();
        }
        $this->notify($ok ? 'converted' : 'conversion_failed', $applicant, [
            'signed_at' => $row->completed_at,
            'student_id' => $student->id ?? $studentId,
            'registration_no' => $student->registration_no ?? null,
            'student_url' => $student ? route('student.show', $student->id) : null,
            'fee_note' => $ok ? $row->outcome_note : null,
            'failed_job' => $failed->job_name ?? null,
            'error' => $ok ? null : ($failed->message ?? $error),
            'batch_id' => $batchId,
        ], $row);
    }

    /** @return string[] the proof of ID details still missing */
    private function missingProof(Applicant $applicant): array
    {
        $proof = $applicant->proof;
        $missing = [];
        if (empty($proof->proof_type)) {
            $missing[] = 'the proof of ID type';
        }
        if (empty($proof->proof_id)) {
            $missing[] = 'the ID number';
        }
        if (empty($proof->proof_expiredate)) {
            $missing[] = 'the ID expiry date';
        }

        return $missing;
    }

    private function setStatus(Applicant $applicant, int $statusId, int $actorId): void
    {
        $old = $applicant->status_id;
        $applicant->status_id = $statusId;
        $applicant->save();
        ApplicantArchive::create([
            'applicant_id' => $applicant->id,
            'table' => 'applicants',
            'field_name' => 'status_id',
            'field_value' => $old,
            'field_new_value' => $statusId,
            'created_by' => $actorId,
        ]);
    }

    private function outcome(Row $row, string $outcome, string $note): array
    {
        $row->fill(['outcome' => $outcome, 'outcome_note' => $note, 'outcome_at' => now()])->save();

        return ['state' => $outcome, 'message' => $note];
    }

    /** The applicant's form sent after this one, if any (only the latest form changes the status). */
    public function newerForm(Row $row): ?Row
    {
        $sentAt = $row->sent_at ?? $row->created_at;

        return Row::where('applicant_id', $row->applicant_id)
            ->where('id', '!=', (int) $row->id)
            ->where(fn ($q) => $q->where('sent_at', '>', $sentAt)->orWhere(fn ($t) => $t->where('sent_at', $sentAt)->where('id', '>', (int) $row->id)))
            ->orderByDesc('sent_at')
            ->first();
    }

    private const NOTIFICATION_TYPES = [
        'converted' => AdmissionNotification::TYPE_OFFER_ACCEPTED,
        'conversion_failed' => AdmissionNotification::TYPE_CONVERSION_FAILED,
        'held' => AdmissionNotification::TYPE_PROOF_MISSING,
        'rejected' => AdmissionNotification::TYPE_OFFER_REJECTED,
    ];

    /**
     * Email DOCUSEAL_NOTIFY_EMAIL and keep the message in admission_notifications. An offer
     * rejection also goes to the staff member who sent the form (not when the system sent it).
     */
    private function notify(string $kind, Applicant $applicant, array $details, ?Row $row = null): void
    {
        $to = array_values(array_filter(array_map('trim', explode(',', (string) config('services.docuseal.notify_email')))));
        if ($kind === 'rejected' && $row && (int) $row->sent_by > 0 && (int) $row->sent_by !== (int) config('tasks.system_user_id', 1)) {
            $sender = \App\Models\User::find($row->sent_by)?->email;
            if ($sender && !in_array(strtolower($sender), array_map('strtolower', $to), true)) {
                $to[] = $sender;
            }
        }
        // (previous body moved into deliver(), unchanged, so the applicant and agent emails are stored the same way)
        $this->deliver(new OfferAcceptanceUpdate($kind, $applicant, $details), $to, $applicant, $row, self::NOTIFICATION_TYPES[$kind] ?? $kind, $details, 'DOCUSEAL_NOTIFY_EMAIL is empty.', 'Admin email "'.$kind.'"');
    }

    /** The offer was rejected: a confirmation to the applicant, and a notice to their agent if they have one. */
    private function notifyParties(Applicant $applicant, array $details, ?Row $row = null): void
    {
        $applicant->loadMissing(['users', 'agent.agent']);
        $type = AdmissionNotification::TYPE_OFFER_REJECTED;

        $email = trim((string) ($applicant->users->email ?? ''));
        $this->deliver(new OfferDeclinedNotice('applicant', $applicant, $details), $email !== '' ? [$email] : [], $applicant, $row, $type, $details + ['audience' => 'applicant'], 'The applicant has no email address.', 'Applicant email "offer declined"');

        $agent = trim((string) ($applicant->agent->email ?? ''));
        if ($agent !== '') {
            $this->deliver(new OfferDeclinedNotice('agent', $applicant, $details), [$agent], $applicant, $row, $type, $details + ['audience' => 'agent'], '', 'Agent email "offer declined"');
        }
    }

    /** Send one email and keep it in admission_notifications (sent / failed / not sent). */
    private function deliver(\Illuminate\Mail\Mailable $mailable, array $to, Applicant $applicant, ?Row $row, string $type, array $details, string $noRecipient, string $label): void
    {
        $body = null;
        try {
            $body = $mailable->renderForRecord();
        } catch (Throwable $e) {
            Log::warning('[DocuSeal] '.$label.' could not be rendered for the record: '.$e->getMessage());
        }

        $notification = null;
        try {
            $notification = AdmissionNotification::create([
                'applicant_id' => $applicant->id,
                'user_id' => $row->sent_by ?? null,
                'source' => 'docuseal',
                'docuseal_submission_id' => $row->id ?? null,
                'type' => $type,
                'channel' => 'email',
                'recipients' => implode(', ', $to),
                'subject' => $mailable->copy['subject'] ?? null,
                'body' => $body,
                'data' => array_map(fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d H:i:s') : $v, $details + ['submission_id' => $row->submission_id ?? null]),
                'status' => empty($to) ? AdmissionNotification::STATUS_NOT_SENT : AdmissionNotification::STATUS_PENDING,
                'error' => empty($to) ? $noRecipient : null,
            ]);
        } catch (Throwable $e) {
            Log::error('[DocuSeal] '.$label.' for applicant #'.$applicant->id.' not stored: '.$e->getMessage());
        }
        if (empty($to)) {
            return;
        }

        try {
            Mail::to($to)->send($mailable);
            $notification?->update(['status' => AdmissionNotification::STATUS_SENT, 'sent_at' => now()]);
        } catch (Throwable $e) {
            $notification?->update(['status' => AdmissionNotification::STATUS_FAILED, 'error' => mb_substr($e->getMessage(), 0, 2000)]);
            Log::error('[DocuSeal] '.$label.' for applicant #'.$applicant->id.' not sent: '.$e->getMessage());
        }
    }
}
