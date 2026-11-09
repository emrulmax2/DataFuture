<?php

namespace App\Jobs;

use App\Models\ApplicantDocusealSubmission;
use App\Services\DocuSeal\OfferAcceptanceWorkflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

/** A DocuSeal webhook for one of our offer acceptance forms (DocusealWebhookController). */
class ProcessDocusealSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 60;

    public function __construct(public int $rowId, public array $hints = [])
    {
    }

    /** Two webhooks for the same form (form.completed + submission.completed) run one after the other. */
    public function middleware(): array
    {
        return [(new WithoutOverlapping('docuseal-submission-'.$this->rowId))->releaseAfter(30)->expireAfter(600)];
    }

    public function handle(OfferAcceptanceWorkflow $workflow): void
    {
        $row = ApplicantDocusealSubmission::find($this->rowId);
        if ($row) {
            $workflow->process($row, $this->hints);
        }
    }
}
