<?php

namespace App\Services\DocuSeal;

use App\Models\Applicant;
use App\Models\ApplicantDocusealSubmission as Row;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The Decline button in the DocuSeal offer acceptance email (user, 27 Oct 2026). The email
 * template links to /offer/decline/{{submitter.slug}}; the slug is the applicant's own key,
 * the last part of the signing link we keep in applicant_docuseal_submissions.signing_url.
 *
 * Opening the link only shows a confirmation page (mail scanners open links). Confirming:
 *   1. asks DocuSeal for the form first: signed → handled as signed, never rejected;
 *   2. archives the submission on DocuSeal, so the signing link stops working;
 *   3. runs the same decline as the DocuSeal form's own Decline button
 *      (OfferAcceptanceWorkflow::decline: Offer Rejected + emails to all parties).
 */
class OfferDecline
{
    public const OPEN = 'open';
    public const DECLINED = 'declined';
    public const SIGNED = 'signed';
    public const CLOSED = 'closed';
    public const ERROR = 'error';

    public function __construct(
        private OfferAcceptanceDocuments $documents,
        private OfferAcceptanceWorkflow $workflow,
    ) {
    }

    public function find(string $slug): ?Row
    {
        if (!preg_match('/^[A-Za-z0-9_-]{6,64}$/', $slug)) {
            return null;
        }

        return Row::where('signing_url', 'like', '%/s/'.str_replace('_', '\\_', $slug))->orderByDesc('id')->first();
    }

    /** What the link can do now: open (can be declined), declined, signed or closed. */
    public function status(Row $row): string
    {
        if ($this->workflow->newerForm($row)) {
            return self::CLOSED; // a newer offer acceptance form was sent
        }
        if ($row->state === Row::STATE_DECLINED || $row->outcome === Row::OUTCOME_REJECTED) {
            return self::DECLINED;
        }
        if (in_array($row->state, [Row::STATE_STORED, Row::STATE_SKIPPED], true) || $row->docuseal_status === 'completed') {
            return self::SIGNED;
        }
        if ($row->state === Row::STATE_EXPIRED) {
            return self::CLOSED;
        }
        $status = (int) Applicant::whereKey($row->applicant_id)->value('status_id');

        return $status === OfferAcceptanceWorkflow::OFFER_PLACED ? self::OPEN : self::CLOSED;
    }

    /** @return array{state:string,message:string} */
    public function decline(Row $row, ?string $reason): array
    {
        // 1. DocuSeal's word first: the form may have been signed (or declined) a moment ago.
        if (in_array($row->state, [Row::STATE_WAITING, Row::STATE_FAILED], true)) {
            $this->documents->check($row);
            $row->refresh();
        }
        if ($row->state === Row::STATE_FAILED) {
            return ['state' => self::ERROR, 'message' => 'DocuSeal could not be reached: '.$row->last_error];
        }
        if (in_array($row->state, [Row::STATE_STORED, Row::STATE_SKIPPED], true)) {
            $this->workflow->process($row);

            return ['state' => self::SIGNED, 'message' => 'The form is already signed.'];
        }
        if ($row->state === Row::STATE_DECLINED) {
            // declined on DocuSeal itself: the usual path, with the reason given here if DocuSeal had none
            $this->workflow->process($row, ['decline_reason' => $reason]);

            return ['state' => self::DECLINED, 'message' => 'Declined.'];
        }
        if (($status = $this->status($row)) !== self::OPEN) {
            return ['state' => $status, 'message' => 'The form can no longer be declined.'];
        }

        // 2. Withdraw the form, so its link cannot be signed afterwards.
        $withdrawn = true;
        try {
            $this->documents->archiveSubmission((int) $row->submission_id);
        } catch (Throwable $e) {
            $withdrawn = false;
            Log::warning('[DocuSeal] Offer declined from the email, but submission '.$row->submission_id.' was not archived: '.$e->getMessage(), ['applicant_id' => $row->applicant_id]);
        }

        // 3. Declined, exactly as the DocuSeal form's own Decline button does.
        $row->fill([
            'state' => Row::STATE_DECLINED,
            'docuseal_status' => $withdrawn ? 'archived' : $row->docuseal_status,
            'decline_reason' => $reason !== null ? mb_substr($reason, 0, 2000) : null,
            'last_checked_at' => now(),
            'last_error' => $withdrawn ? null : 'Declined from the offer email, but the DocuSeal submission could not be archived.',
        ])->save();
        $result = $this->workflow->decline($row, 'email_link', ['withdrawn' => $withdrawn]);

        return ['state' => self::DECLINED, 'message' => $result['message']];
    }

    /** What the page shows about the offer. */
    public function summary(Row $row): array
    {
        $applicant = Applicant::with(['course.creation.course', 'course.semester'])->find($row->applicant_id);

        return [
            'firstName' => trim((string) ($applicant->first_name ?? '')),
            'ref' => $applicant->application_no ?? null,
            'course' => $applicant->course->creation->course->name ?? null,
            'intake' => $applicant->course->semester->name ?? null,
            'signingUrl' => $row->signing_url,
            'reason' => $row->decline_reason,
        ];
    }
}
