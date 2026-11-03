<?php

namespace App\Services\SlcRegistration;

/**
 * Where /slc-registration keeps the files it is given (SIS reports, remittance files, the batch
 * files it builds): the local disk on a developer's machine and in tests, S3 on the server.
 * The module's own copy, so it doesn't depend on the admission code's ApplicantDocumentDisk
 * (3 Nov 2026: the server had the SLC module without the admission work).
 */
class FileDisk
{
    public static function name(): string
    {
        return app()->environment(['local', 'testing']) ? 'local' : 's3';
    }
}
