<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Http\Controllers\Api\Student\StudentApiController;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Shared ground for the "Do it online" forms in the student app.
 *
 * The app draws its own screens with React Native inputs, so nothing here
 * describes a layout: each endpoint hands over the few values the app cannot
 * know (the student's own record, the lists it must choose from) and takes a
 * submission back. What then happens to that submission — the row, the Service
 * Desk ticket, the receipt email, the retry if Operations is down — is the same
 * code the website uses, untouched.
 *
 * Validation is written out per form rather than shared with the web
 * controller: the web layer answers with a redirect and a flash bag, this one
 * with a 422 and a field map, and a rule that drifts between them is a bug the
 * app would feel first. They are deliberately word-for-word the same.
 */
abstract class BaseFormController extends StudentApiController
{
    /** The forms_table id this endpoint belongs to, for the stored row. */
    abstract protected function formId(): int;

    /**
     * The read-only block every form shows at the top.
     *
     * Named as the forms name them, so the app can print them without a
     * lookup table. A student with no start date on their enrolment gets the
     * intake instead — four relations in five carry no start date.
     *
     * @return array<string, mixed>
     */
    protected function studentBlock(Student $student): array
    {
        $contact = $student->contact;

        return [
            'id' => $student->id,
            'full_name' => trim(($student->title->name ?? '').' '.$student->first_name.' '.$student->last_name),
            'name' => trim($student->first_name.' '.$student->last_name),
            'student_id' => $student->registration_no,
            'email' => $contact?->institutional_email
                ?: ($student->users->email ?? $contact?->personal_email),
            'mobile' => $contact?->mobile,
            'address' => $this->correspondenceAddress($student),
            'course' => $student->activeCR->course->name ?? null,
            'course_start_date' => $this->dateOrNull($student->activeCR->course_start_date ?? null),
            'intake' => $student->activeCR->semester->name ?? null,
        ];
    }

    /** The term-time address the portal shows, as one line. */
    protected function correspondenceAddress(Student $student): ?string
    {
        $address = $student->contact?->term_time_address_id ? $student->contact->termaddress : null;

        if (! $address) {
            return null;
        }

        return implode(', ', array_filter([
            $address->address_line_1 ?? null,
            $address->address_line_2 ?? null,
            $address->city ?? null,
            $address->state ?? null,
            $address->post_code ?? null,
            $address->country ?? null,
        ])) ?: null;
    }

    /** course_start_date is an empty string on most relations rather than null. */
    protected function dateOrNull($value): ?string
    {
        return $value && ! empty((string) $value) ? date('Y-m-d', strtotime((string) $value)) : null;
    }

    /**
     * The student, with everything these forms read, in one query.
     *
     * The base class resolves who it is; this adds the relations so a form is
     * not answered with a dozen round trips.
     */
    protected function formStudent(Request $request): ?Student
    {
        $student = $this->student($request);

        return $student
            ? $student->load('users', 'contact.termaddress', 'activeCR.course', 'activeCR.semester')
            : null;
    }

    /**
     * The app sends dates as YYYY-MM-DD; the web form and the stored rows use
     * DD-MM-YYYY through the models. Converted once, here.
     */
    protected function fromIsoDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $date = date_create_immutable_from_format('Y-m-d', (string) $value);

        return $date ? $date->format('Y-m-d') : null;
    }

    /**
     * The files as uploaded, read once for the ticket.
     *
     * Read before they are moved to storage: storeAs() moves the temp file, and
     * the ticket carries the bytes rather than a link back here.
     *
     * @return array<int, array{name: string, contents: string}>
     */
    protected function uploadedContents(Request $request, string $field = 'documents'): array
    {
        return collect($request->file($field, []))
            ->filter()
            ->map(fn ($document) => [
                'name' => $document->getClientOriginalName(),
                'contents' => file_get_contents($document->getRealPath()),
            ])
            ->values()
            ->all();
    }

    /**
     * Put one upload away, on S3 or, failing that, locally.
     *
     * S3 is where student documents live, but it can refuse — bad credentials,
     * an outage, a bucket that is not there — and when it does the file must
     * not simply vanish: it is part of a request the student has already made.
     *
     * @return array{disk: string, key: string, url: ?string}|null
     */
    protected function storeDocument($document, int $studentId, string $documentName, int $requestId): ?array
    {
        $folder = 'students/'.$studentId;

        foreach ([['s3', 'public/'.$folder], ['public', $folder]] as [$disk, $directory]) {
            try {
                $key = $document->storeAs($directory, $documentName, $disk);
            } catch (\Throwable $e) {
                $key = false;
                Log::warning('[Student app form] Document store failed on '.$disk.'.', [
                    'request' => $requestId, 'error' => $e->getMessage(),
                ]);
            }

            if ($key === false) {
                continue;
            }

            try {
                $url = Storage::disk($disk)->url($key);
            } catch (\Throwable $e) {
                $url = null;
            }

            if ($disk !== 's3') {
                Log::warning('[Student app form] Document stored locally; S3 would not take it.', [
                    'request' => $requestId, 'key' => $key,
                ]);
            }

            return ['disk' => $disk, 'key' => $key, 'url' => $url];
        }

        Log::error('[Student app form] Document could not be stored anywhere.', [
            'request' => $requestId, 'file' => $document->getClientOriginalName(),
        ]);

        return null;
    }

    /**
     * Store each uploaded file against a request row.
     *
     * @param  callable  $write  given the stored file's details, writes the row
     */
    protected function storeDocuments(Request $request, Student $student, int $requestId, callable $write, string $field = 'documents'): void
    {
        if (! $request->hasFile($field)) {
            return;
        }

        foreach ($request->file($field) as $document) {
            $documentName = time().'_'.$document->getClientOriginalName();
            $stored = $this->storeDocument($document, $student->id, $documentName, $requestId);

            if (! $stored) {
                continue;
            }

            $write([
                'doc_type' => $document->getClientOriginalExtension(),
                'disk_type' => $stored['disk'],
                'path' => $stored['url'],
                'storage_key' => $stored['key'],
                'display_file_name' => $document->getClientOriginalName(),
                'current_file_name' => $documentName,
            ]);
        }
    }

    /**
     * What every submission answers with.
     *
     * The reference is the Service Desk ticket, so it only exists once
     * Operations has taken the request. When it has not — Operations down, a
     * timeout — the row is saved and queued for retry, and the app is told
     * exactly that rather than being handed a failure: the student's
     * submission is safe either way.
     */
    protected function submitted($row, bool $raised): \Illuminate\Http\JsonResponse
    {
        return $this->ok([
            'id' => $row->id,
            'reference' => $row->ticket_ref,
            'status' => $row->ticket_status,
            'submitted_at' => optional($row->created_at)->toIso8601String(),
            'message' => $raised
                ? 'Your request has been submitted. We have emailed you a copy.'
                : 'Your request has been received. We will email you the reference shortly.',
        ], 201);
    }
}
