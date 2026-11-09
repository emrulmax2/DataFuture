<?php

namespace App\Http\Controllers;

use App\Services\ClassRoutine\ClassRoutineLink;
use App\Support\ApplicantDocumentDisk;
use Illuminate\Support\Facades\Storage;

/**
 * Opens the class routine PDF from the link in the student's email and text message
 * (no sign-in; the link is signed and expires, see ClassRoutineLink).
 */
class ClassRoutineDownloadController extends Controller
{
    public function show(string $token)
    {
        $document = ClassRoutineLink::resolve($token);
        abort_unless($document, 404);

        $disk = ApplicantDocumentDisk::of($document);
        $path = 'public/students/'.$document->student_id.'/'.$document->current_file_name;
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return response(Storage::disk($disk)->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Class_Routine.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
