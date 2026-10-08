<?php

namespace App\Services;

use App\Mail\StudentComplaintReceived;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;

/**
 * Stage 1 complaints, as Service Desk tickets.
 *
 * The complaint itself leads, in the student's own words — whoever picks this
 * up reads it before anything else. What has already been tried comes next,
 * because a complaint that has been round the department once is handled
 * differently from one that has not.
 */
class ComplaintTicket extends PortalFormTicket
{
    protected function configKey(): string
    {
        return 'complaint';
    }

    protected function referencePrefix(): string
    {
        return 'CMP';
    }

    protected function receipt(Model $row, Student $student): Mailable
    {
        return new StudentComplaintReceived(
            complaint: $row->load('documents'),
            studentName: $this->studentName($student),
            registrationNo: $student->registration_no,
            studentEmail: $this->studentEmail($student),
            studentMobile: $student->contact?->mobile,
            logoPath: $this->logoPath(),
        );
    }

    protected function subject(Model $row, Student $student): string
    {
        return mb_substr(
            'Complaint (Stage 1) — '.($student->registration_no ?: $student->id)
                .' — '.($row->course_name ?: 'course not recorded'),
            0,
            200
        );
    }

    protected function body(Model $row, Student $student, array $attached = []): string
    {
        $documents = $attached !== []
            ? collect($attached)
            : $row->documents->pluck('display_file_name')->filter();

        $lines = [
            'Raised from the student portal (Complaint Form Stage 1).',
            '',
            'Complainant:         '.$this->studentName($student).' ('.($student->registration_no ?: 'no registration number').')',
            'Relationship to LCC: '.($row->relationship ?: 'Student'),
            'Student email:       '.($this->studentEmail($student) ?: 'Not recorded'),
            'Contact number:      '.($student->contact?->mobile ?: 'Not recorded'),
            'Programme / course:  '.($row->course_name ?: 'Not recorded'),
            '',
            'The complaint:',
            trim((string) $row->complaint_details),
            '',
            'Spoken to anyone already: '.$row->spokenToLabel(),
            '',
            'What has been tried, and why they remain dissatisfied:',
            trim((string) $row->attempts),
            '',
            'What they would like to happen:',
            trim((string) $row->desired_outcome),
            '',
            'Supporting documents: '.($documents->count() ? $documents->implode(', ') : 'None provided'),
            /* The declaration is the student's consent to this being discussed
               inside the College, so it is recorded with who gave it. */
            'Declaration:         '.($row->declaration_accepted
                ? 'Confirmed by '.($row->declaration_name ?: $this->studentName($student))
                    .' on '.optional($row->created_at)->format('j F Y, g:ia')
                : 'Not confirmed'),
            'Submitted from:      '.$this->device($row),
            'IP address:          '.($row->ip_address ?: 'Not recorded'),
            '',
            'Portal request reference: '.$this->referencePrefix().'-'.$row->id,
        ];

        return implode("\n", $lines);
    }

    private function device(Model $row): string
    {
        $parts = array_filter([$row->device_type, $row->device_os, $row->device_browser, $row->device_screen]);

        return $parts ? implode(' · ', $parts) : 'Not recorded';
    }
}
