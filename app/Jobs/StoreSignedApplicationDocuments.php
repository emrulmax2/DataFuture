<?php

namespace App\Jobs;

use App\Services\Admission\SignedApplicationDocuments;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * After the applicant submits the e-signature: store the signed application form and
 * the audit trail PDFs in the applicant's Uploads ("Other"). Queued so the applicant
 * does not wait for the two PDFs; a retry only stores what is still missing.
 */
class StoreSignedApplicationDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(public int $applicantId)
    {
    }

    public function handle(SignedApplicationDocuments $documents): void
    {
        $documents->store($this->applicantId);
    }

    public function failed(Throwable $e): void
    {
        Log::warning('[E-Signature] Signed application documents not stored for applicant #'.$this->applicantId.': '.$e->getMessage());
    }
}
