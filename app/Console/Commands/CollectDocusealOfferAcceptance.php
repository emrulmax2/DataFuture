<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\ApplicantDocusealSubmission;
use App\Services\DocuSeal\OfferAcceptanceDocuments;
use App\Services\DocuSeal\OfferAcceptanceWorkflow;
use Illuminate\Console\Command;
use Throwable;

/**
 * The backstop for the DocuSeal webhook (and the only path on a machine DocuSeal
 * cannot reach): runs OfferAcceptanceWorkflow for every form that still needs work —
 * signed → uploads + Offer Accepted + conversion + admin email; declined → Offer
 * Rejected + admin email. --dry-run only fetches and downloads. Scheduled every ten
 * minutes; also handy by hand:
 *
 *   php artisan docuseal:collect-signed-offers                          # every open form
 *   php artisan docuseal:collect-signed-offers --applicant=3561         # one applicant
 *   php artisan docuseal:collect-signed-offers --submission=123 --dry-run
 *   php artisan docuseal:collect-signed-offers --link --submission=123 --applicant=3561
 *        (a form sent before sends were recorded: register it, then check it)
 */
class CollectDocusealOfferAcceptance extends Command
{
    protected $signature = 'docuseal:collect-signed-offers
        {--applicant= : Only this applicant\'s forms}
        {--submission= : Only this DocuSeal submission id}
        {--link : Register --submission against --applicant before checking it}
        {--dry-run : Fetch and download, but store nothing}';

    protected $description = 'Process DocuSeal offer acceptance forms: store signed forms + audit logs, then accept or reject the offer';

    public function handle(OfferAcceptanceDocuments $documents, OfferAcceptanceWorkflow $workflow): int
    {
        $applicantId = $this->option('applicant') ? (int) $this->option('applicant') : null;
        $submissionId = $this->option('submission') ? (int) $this->option('submission') : null;
        $dryRun = (bool) $this->option('dry-run');

        if ($this->option('link')) {
            if (!$applicantId || !$submissionId) {
                $this->error('--link needs both --submission and --applicant.');

                return self::FAILURE;
            }
            if (!Applicant::find($applicantId)) {
                $this->error('Applicant #'.$applicantId.' does not exist.');

                return self::FAILURE;
            }
            $existing = ApplicantDocusealSubmission::where('submission_id', $submissionId)->first();
            if ($existing && (int) $existing->applicant_id !== $applicantId) {
                $this->error('Submission '.$submissionId.' is already recorded for applicant #'.$existing->applicant_id.'.');

                return self::FAILURE;
            }
            try {
                $remote = $documents->fetchSubmission($submissionId);
            } catch (Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
            $applicantSide = collect($remote['submitters'] ?? [])->firstWhere('role', 'Applicant') ?? [];
            if (!$dryRun) {
                $documents->record($applicantId, [
                    'submission_id' => $submissionId,
                    'template_id' => $remote['template']['id'] ?? null,
                    'applicant_email' => $applicantSide['email'] ?? null,
                    'applicant_signing_url' => $applicantSide['embed_src'] ?? null,
                ], null, $remote['created_at'] ?? null);
            }
            $this->line('Linked submission '.$submissionId.' ('.($applicantSide['email'] ?? 'no applicant email').', sent '.($remote['created_at'] ?? '?').') to applicant #'.$applicantId.'.');
        }

        $query = ApplicantDocusealSubmission::query()->orderBy('id');
        if ($submissionId) {
            $query->where('submission_id', $submissionId);
        } else {
            $dryRun ? $query->open() : $query->needsWork();
        }
        if ($applicantId) {
            $query->where('applicant_id', $applicantId);
        }
        $rows = $query->limit(200)->get();

        if ($rows->isEmpty() && $submissionId && $dryRun) {
            // A dry run may look at any submission, recorded or not.
            $rows = collect([new ApplicantDocusealSubmission(['submission_id' => $submissionId, 'applicant_id' => $applicantId ?? 0, 'state' => ApplicantDocusealSubmission::STATE_WAITING])]);
        }
        if ($rows->isEmpty()) {
            $this->line('Nothing to check.');

            return self::SUCCESS;
        }

        $table = [];
        foreach ($rows as $row) {
            $result = $dryRun ? $documents->check($row, true) : $workflow->process($row);
            $table[] = [$row->applicant_id ?: '—', $row->submission_id, $result['state'], $result['message']];
        }
        $this->table(['Applicant', 'Submission', 'Result', 'Detail'], $table);

        return self::SUCCESS;
    }
}
