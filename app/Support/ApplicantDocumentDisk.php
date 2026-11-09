<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Where applicant documents live. On a local or testing install they are
 * written to and served from the local disk (storage/app/public/applicants,
 * reachable through public/storage); everywhere else they stay on S3.
 *
 * Each applicant_documents row records its disk in `disk_type`, so a file
 * is always served from the disk it was written to; rows from before this
 * column was filled are S3 files.
 */
class ApplicantDocumentDisk
{
    public static function name(): string
    {
        return app()->environment(['local', 'testing']) ? 'local' : 's3';
    }

    /** The disk a stored document lives on. */
    public static function of($document): string
    {
        $disk = (string) ($document->disk_type ?? '');

        return $disk !== '' ? $disk : 's3';
    }

    public static function path($applicantId, string $fileName): string
    {
        return 'public/applicants/'.$applicantId.'/'.$fileName;
    }

    /** A URL the browser can open: a signed, short-lived one for S3, a /storage URL locally. */
    public static function url($document, int $minutes = 5): string
    {
        if (!$document || empty($document->current_file_name)) {
            return '';
        }
        $path = self::path($document->applicant_id, $document->current_file_name);
        if (self::of($document) === 's3') {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes($minutes));
        }

        return url(Storage::disk(self::of($document))->url($path));
    }
}
