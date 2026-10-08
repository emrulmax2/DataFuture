<?php

namespace App\Services;

use App\Mail\StudentCourseChangeReceived;
use App\Models\Student;
use App\Models\StudentCourseChangeRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Course change requests, as Service Desk tickets.
 *
 * The ordering, idempotency and failure handling are in PortalFormTicket; what
 * is here is what makes this form different: where it goes, what the ticket
 * says, and the receipt the student gets.
 */
class CourseChangeTicket extends PortalFormTicket
{
    protected function configKey(): string
    {
        return 'course_change';
    }

    protected function referencePrefix(): string
    {
        return 'CCR';
    }

    protected function receipt(Model $row, Student $student): Mailable
    {
        return new StudentCourseChangeReceived(
            courseChange: $row->load('documents'),
            studentName: $this->studentName($student),
            registrationNo: $student->registration_no,
            logoPath: $this->logoPath(),
            currentIntake: $student->activeCR->semester->name ?? null,
        );
    }

    protected function subject(Model $row, Student $student): string
    {
        return mb_substr(
            'Course change request — '.($student->registration_no ?: $student->id)
                .' — '.($row->proposed_course_name ?: 'course not named'),
            0,
            200
        );
    }

    /**
     * What the ticket says.
     *
     * Plain text laid out as a list of facts: the desk reads this first and
     * acts on it, so the current course, the requested one and the date they
     * want it from come before the student's own words.
     */
    protected function body(Model $row, Student $student, array $attached = []): string
    {
        $documents = $attached !== []
            ? collect($attached)
            : $row->documents->pluck('display_file_name')->filter();

        $lines = [
            'Raised from the student portal (Course Change Request Form).',
            '',
            'Student:             '.$this->studentName($student).' ('.($student->registration_no ?: 'no registration number').')',
            'Student email:       '.($this->studentEmail($student) ?: 'Not recorded'),
            'Student mobile:      '.($student->contact?->mobile ?: 'Not recorded'),
            '',
            'Current course:      '.($row->current_course_name ?: 'Not recorded'),
            /* Most enrolments carry no start date, so the intake stands in. */
            'Current start date:  '.($row->current_course_start_date
                ? $this->readableDate($row->current_course_start_date)
                : (($student->activeCR->semester->name ?? null) ? $student->activeCR->semester->name.' intake' : 'Not recorded')),
            'Requested course:    '.($row->proposed_course_name ?: 'Not recorded'),
            'Requested start:     '.$this->readableDate($row->proposed_start_date),
            '',
            'Reasons for change:',
            trim((string) $row->reason),
            '',
            'Supporting documents: '.($documents->count() ? $documents->implode(', ') : 'None provided'),
            'Declaration:          '.($row->declaration_accepted
                ? 'Confirmed by '.($row->declaration_name ?: $this->studentName($student))
                    .' on '.optional($row->created_at)->format('j F Y, g:ia')
                : 'Not confirmed'),
            '',
            'Portal request reference: CCR-'.$row->id,
        ];

        return implode("\n", $lines);
    }

    /**
     * The files a student attached, read back out of storage.
     *
     * Used by the retry: the uploads are long gone from the request by then,
     * but they are on the disk the documents row points at.
     *
     * @return array<int, array{name: string, contents: string}>
     */
    public function storedFiles(StudentCourseChangeRequest $courseChange): array
    {
        return $courseChange->documents->map(function ($document) use ($courseChange) {
            /* Older rows predate storage_key and were always on S3 under this
               convention; newer ones say where they went. */
            $path = $document->storage_key
                ?: 'public/students/'.$courseChange->student_id.'/'.$document->current_file_name;

            try {
                $contents = Storage::disk($document->disk_type ?: 's3')->get($path);
            } catch (\Throwable $e) {
                Log::warning('[Course change] Attachment could not be read back.', [
                    'request' => $courseChange->id, 'path' => $path, 'error' => $e->getMessage(),
                ]);

                return null;
            }

            return $contents === null ? null : [
                'name' => $document->display_file_name ?: $document->current_file_name,
                'contents' => $contents,
            ];
        })->filter()->values()->all();
    }
}
