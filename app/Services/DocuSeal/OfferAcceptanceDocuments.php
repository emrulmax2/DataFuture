<?php

namespace App\Services\DocuSeal;

use App\Models\ApplicantDocument;
use App\Models\ApplicantDocusealSubmission;
use App\Models\DocumentSettings;
use App\Support\ApplicantDocumentDisk;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * The DocuSeal offer acceptance form, after signing: once both parties are done
 * (DocuSeal reports the submission "completed" — the college side is pre-completed
 * when the form is sent, so this is when the applicant signs), the signed form and
 * DocuSeal's audit log are downloaded and stored as two applicant documents of the
 * "Offer Acceptance / Audit Trail" type, named exactly like the manual uploads:
 *   "Offer Acceptance / Audit Trail - Offer Acceptance"  (hard copy: No)
 *   "Offer Acceptance / Audit Trail - Audit Trail"       (hard copy: No)
 * Files follow ApplicantDocumentDisk (local disk locally, S3 on the servers).
 * Only one signed form is stored per applicant; a later one is marked skipped (unless the
 * stored one's upload has since been deleted, then the new one is stored).
 */
class OfferAcceptanceDocuments
{
    public const TYPE_NAME = 'Offer Acceptance / Audit Trail';
    public const OFFER_NAME = 'Offer Acceptance';
    public const AUDIT_NAME = 'Audit Trail';

    /** Remember a form that was just sent (AdmissionController::sendDocusealForm). */
    public function record(int $applicantId, array $sent, ?int $sentBy, $sentAt = null): ?ApplicantDocusealSubmission
    {
        if (empty($sent['submission_id'])) {
            return null;
        }

        return ApplicantDocusealSubmission::firstOrCreate(['submission_id' => (int) $sent['submission_id']], [
            'applicant_id' => $applicantId,
            'template_id' => $sent['template_id'] ?? null,
            'applicant_email' => $sent['applicant_email'] ?? null,
            'signing_url' => $sent['applicant_signing_url'] ?? null,
            'state' => ApplicantDocusealSubmission::STATE_WAITING,
            'docuseal_status' => 'pending',
            'sent_by' => $sentBy,
            'sent_at' => $sentAt ? Carbon::parse($sentAt)->setTimezone(config('app.timezone')) : now(),
        ]);
    }

    /** GET /submissions/{id}. */
    public function fetchSubmission(int $submissionId): array
    {
        $key = config('services.docuseal.api_key');
        if (empty($key)) {
            throw new RuntimeException('DOCUSEAL_API_KEY is not configured.');
        }
        $res = Http::withHeaders(['X-Auth-Token' => $key, 'Accept' => 'application/json'])
            ->timeout(30)
            ->get(rtrim((string) config('services.docuseal.api_url', 'https://api.docuseal.co'), '/').'/submissions/'.$submissionId);
        if (!$res->successful()) {
            throw new RuntimeException('DocuSeal answered HTTP '.$res->status().' for submission '.$submissionId.'.');
        }
        $body = $res->json();
        if (!is_array($body) || !isset($body['id'])) {
            throw new RuntimeException('DocuSeal returned an unexpected answer for submission '.$submissionId.'.');
        }

        return $body;
    }

    /**
     * DELETE /submissions/{id}: DocuSeal archives the submission, so its signing link stops
     * working (used when the applicant declines from the offer email, OfferDecline).
     */
    public function archiveSubmission(int $submissionId): void
    {
        $key = config('services.docuseal.api_key');
        if (empty($key)) {
            throw new RuntimeException('DOCUSEAL_API_KEY is not configured.');
        }
        $res = Http::withHeaders(['X-Auth-Token' => $key, 'Accept' => 'application/json'])
            ->timeout(30)
            ->delete(rtrim((string) config('services.docuseal.api_url', 'https://api.docuseal.co'), '/').'/submissions/'.$submissionId);
        if (!$res->successful()) {
            throw new RuntimeException('DocuSeal answered HTTP '.$res->status().' when archiving submission '.$submissionId.'.');
        }
    }

    /** The signed form and the audit log as PDF bytes, or an exception saying what is missing. */
    public function downloadPdfs(array $submission): array
    {
        $doc = $submission['documents'][0] ?? null;
        if (empty($doc['url'])) {
            throw new RuntimeException('DocuSeal has no signed document for submission '.$submission['id'].' yet.');
        }
        if (empty($submission['audit_log_url'])) {
            throw new RuntimeException('DocuSeal has no audit log for submission '.$submission['id'].' yet.');
        }

        return [
            'name' => trim((string) ($doc['name'] ?? '')) ?: 'Offer acceptance form',
            'offer' => $this->downloadPdf($doc['url'], 'the signed form'),
            'audit' => $this->downloadPdf($submission['audit_log_url'], 'the audit log'),
        ];
    }

    /**
     * Check one recorded form and store its documents when it is signed.
     *
     * @return array{state:string,message:string}
     */
    public function check(ApplicantDocusealSubmission $row, bool $dryRun = false): array
    {
        if (!$dryRun) {
            $row->last_checked_at = now();
            $row->checks = (int) $row->checks + 1;
        }
        try {
            $submission = $this->fetchSubmission((int) $row->submission_id);
            $status = (string) ($submission['status'] ?? '');
            $row->docuseal_status = $status ?: $row->docuseal_status;

            if (in_array($status, ['declined', 'expired'], true)) {
                $declinedBy = collect($submission['submitters'] ?? [])->first(fn ($s) => !empty($s['declined_at']));
                if ($declinedBy && empty($row->decline_reason) && !empty($declinedBy['decline_reason'])) {
                    $row->decline_reason = mb_substr((string) $declinedBy['decline_reason'], 0, 2000);
                }

                return $this->finish($row, $status === 'declined' ? ApplicantDocusealSubmission::STATE_DECLINED : ApplicantDocusealSubmission::STATE_EXPIRED, 'DocuSeal reports the form '.$status.'.', $dryRun);
            }
            if ($status !== 'completed') {
                $applicantSide = collect($submission['submitters'] ?? [])->firstWhere('role', 'Applicant');

                return $this->finish($row, ApplicantDocusealSubmission::STATE_WAITING, 'Not signed yet (applicant: '.($applicantSide['status'] ?? 'unknown').').', $dryRun);
            }
            if (!empty($submission['completed_at'])) {
                $row->completed_at = Carbon::parse($submission['completed_at'])->setTimezone(config('app.timezone'));
            }

            // Another signed form already in the uploads (and not deleted there since).
            $other = ApplicantDocusealSubmission::where('applicant_id', $row->applicant_id)
                ->where('id', '!=', (int) $row->id)
                ->where('state', ApplicantDocusealSubmission::STATE_STORED)
                ->whereIn('offer_document_id', ApplicantDocument::where('applicant_id', $row->applicant_id)->select('id'))
                ->first();
            if ($other) {
                return $this->finish($row, ApplicantDocusealSubmission::STATE_SKIPPED, 'Signed, but the signed form from submission '.$other->submission_id.' is already in the uploads.', $dryRun);
            }

            $pdfs = $this->downloadPdfs($submission);
            if ($dryRun) {
                return ['state' => 'dry-run', 'message' => 'Signed. Would store "'.$pdfs['name'].'" ('.$this->kb($pdfs['offer']).') and its audit log ('.$this->kb($pdfs['audit']).').'];
            }

            return $this->store($row, $pdfs);
        } catch (Throwable $e) {
            if ($dryRun) {
                return ['state' => 'failed', 'message' => $e->getMessage()];
            }
            $row->state = ApplicantDocusealSubmission::STATE_FAILED;
            $row->failures = (int) $row->failures + 1;
            $row->last_error = mb_substr($e->getMessage(), 0, 2000);
            $row->save();
            Log::warning('[DocuSeal] Signed offer acceptance not stored for applicant #'.$row->applicant_id.': '.$e->getMessage(), ['submission_id' => $row->submission_id, 'failures' => $row->failures]);

            return ['state' => ApplicantDocusealSubmission::STATE_FAILED, 'message' => $e->getMessage()];
        }
    }

    private function store(ApplicantDocusealSubmission $row, array $pdfs): array
    {
        $disk = ApplicantDocumentDisk::name();
        $setting = DocumentSettings::where('name', self::TYPE_NAME)->first();
        $typeName = $setting->name ?? self::TYPE_NAME;
        $systemUser = (int) config('tasks.system_user_id', 1);
        $base = preg_replace('/[\\\\\/:*?"<>|]+/', '-', $pdfs['name']);
        $stamp = time();
        $files = [
            'offer' => $stamp.'_'.$base.'.pdf',
            'audit' => $stamp.'_Audit Log - '.$base.'.pdf',
        ];
        $written = [];

        try {
            return DB::transaction(function () use ($row, $pdfs, $disk, $setting, $typeName, $systemUser, $files, &$written) {
                $locked = ApplicantDocusealSubmission::whereKey($row->id)->lockForUpdate()->first();
                if ($locked && $locked->state === ApplicantDocusealSubmission::STATE_STORED) {
                    return ['state' => ApplicantDocusealSubmission::STATE_STORED, 'message' => 'Already stored by another run.'];
                }

                $ids = [];
                foreach (['offer' => self::OFFER_NAME, 'audit' => self::AUDIT_NAME] as $key => $label) {
                    $path = ApplicantDocumentDisk::path($row->applicant_id, $files[$key]);
                    if (!Storage::disk($disk)->put($path, $pdfs[$key])) {
                        throw new RuntimeException('Could not write '.$files[$key].' to the '.$disk.' disk.');
                    }
                    $written[] = $path;
                    $ids[$key] = ApplicantDocument::create([
                        'applicant_id' => $row->applicant_id,
                        'document_setting_id' => $setting->id ?? 0,
                        'hard_copy_check' => 0,
                        'doc_type' => 'pdf',
                        'disk_type' => $disk,
                        'path' => Storage::disk($disk)->url($path),
                        'display_file_name' => $typeName.' - '.$label,
                        'current_file_name' => $files[$key],
                        'created_by' => $systemUser,
                    ])->id;
                }

                $row->fill([
                    'state' => ApplicantDocusealSubmission::STATE_STORED,
                    'stored_at' => now(),
                    'offer_document_id' => $ids['offer'],
                    'audit_document_id' => $ids['audit'],
                    'failures' => 0,
                    'last_error' => null,
                ])->save();

                Log::info('[DocuSeal] Signed offer acceptance stored for applicant #'.$row->applicant_id.'.', ['submission_id' => $row->submission_id, 'documents' => $ids, 'disk' => $disk]);

                return ['state' => ApplicantDocusealSubmission::STATE_STORED, 'message' => 'Stored "'.$typeName.' - '.self::OFFER_NAME.'" and "'.$typeName.' - '.self::AUDIT_NAME.'".'];
            });
        } catch (Throwable $e) {
            foreach ($written as $path) {
                Storage::disk($disk)->delete($path);
            }
            throw $e;
        }
    }

    private function finish(ApplicantDocusealSubmission $row, string $state, string $message, bool $dryRun): array
    {
        if (!$dryRun) {
            $row->state = $state;
            $row->failures = 0;
            $row->last_error = $state === ApplicantDocusealSubmission::STATE_SKIPPED ? $message : null;
            $row->save();
        }

        return ['state' => $state, 'message' => $message];
    }

    private function downloadPdf(string $url, string $what): string
    {
        $res = Http::timeout(60)->get($url);
        if (!$res->successful()) {
            throw new RuntimeException('Downloading '.$what.' failed with HTTP '.$res->status().'.');
        }
        $bytes = $res->body();
        if (strncmp($bytes, '%PDF', 4) !== 0) {
            throw new RuntimeException('DocuSeal did not return a PDF for '.$what.'.');
        }

        return $bytes;
    }

    private function kb(string $bytes): string
    {
        return number_format(strlen($bytes) / 1024, 0).' KB';
    }
}
