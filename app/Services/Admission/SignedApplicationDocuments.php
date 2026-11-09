<?php

namespace App\Services\Admission;

use App\Enums\EsignEventType;
use App\Http\Controllers\ApplicanESignatureController;
use App\Http\Controllers\ApplicantProfilePrintController;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantESignature;
use App\Models\ApplicantESignatureEvent;
use App\Models\DocumentSettings;
use App\Support\ApplicantDocumentDisk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Once the applicant e-signs (/applicant/e-signature/…), the signed application form
 * (/applicantprofilepdf/{id}) and the e-signature audit trail
 * (/applicant/e-signature/download/{id}) are stored in the applicant's Uploads under
 * the "Other" document type, the two files staff used to download and upload by hand
 * (user, 27 Oct 2026). Runs from App\Jobs\StoreSignedApplicationDocuments.
 *
 * Files go to ApplicantDocumentDisk::name(): S3 on the servers, the local disk on a
 * local install. A file already stored for this signature is not stored again, so a
 * retry only adds what is missing.
 */
class SignedApplicationDocuments
{
    public const TYPE_NAME = 'Other';
    public const FORM_NAME = 'Signed Application Form';
    public const AUDIT_NAME = 'Signed Application Form Audit Trail';

    /** @return array{stored: string[], skipped: string[]} */
    public function store(int $applicantId): array
    {
        $applicant = Applicant::find($applicantId);
        $esign = ApplicantESignature::where('applicant_id', $applicantId)->first();
        if (!$applicant || !$esign || $esign->status !== 'accepted' || empty($esign->signature)) {
            return ['stored' => [], 'skipped' => ['not signed']];
        }

        $disk = ApplicantDocumentDisk::name();
        $setting = DocumentSettings::where('name', self::TYPE_NAME)->first();
        $typeName = $setting->name ?? self::TYPE_NAME;
        // this signature = the applicant's latest "finalized" event (signed_date is a date only)
        $since = ApplicantESignatureEvent::where('applicant_id', $applicantId)->where('user_type', 'applicant')
            ->where('event_type', EsignEventType::FINALIZED->value)->max('created_at') ?: $esign->updated_at;
        $result = ['stored' => [], 'skipped' => []];

        $builders = [
            self::FORM_NAME => fn () => app(ApplicantProfilePrintController::class)->buildPdf($applicantId),
            self::AUDIT_NAME => fn () => app(ApplicanESignatureController::class)->auditPdf($applicantId),
        ];
        foreach ($builders as $label => $build) {
            $display = $typeName.' - '.$label;
            $already = ApplicantDocument::where('applicant_id', $applicantId)
                ->where('display_file_name', $display)
                ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
                ->exists();
            if ($already) {
                $result['skipped'][] = $display;
                continue;
            }

            [$pdf, $name] = $build();
            $file = time().'_'.preg_replace('/[\\\\\/:*?"<>|]+/', '-', $name);
            $path = ApplicantDocumentDisk::path($applicantId, $file);
            if (!Storage::disk($disk)->put($path, $pdf->output())) {
                throw new RuntimeException('Could not write '.$file.' to the '.$disk.' disk.');
            }
            try {
                ApplicantDocument::create([
                    'applicant_id' => $applicantId,
                    'document_setting_id' => $setting->id ?? 0,
                    'hard_copy_check' => 0,
                    'doc_type' => 'pdf',
                    'disk_type' => $disk,
                    'path' => Storage::disk($disk)->url($path),
                    'display_file_name' => $display,
                    'current_file_name' => $file,
                    'created_by' => (int) config('tasks.system_user_id', 1),
                ]);
            } catch (Throwable $e) {
                Storage::disk($disk)->delete($path);
                throw $e;
            }
            $result['stored'][] = $display;
        }

        if ($result['stored']) {
            Log::info('[E-Signature] Signed application documents stored for applicant #'.$applicantId.'.', $result + ['disk' => $disk]);
        }

        return $result;
    }
}
