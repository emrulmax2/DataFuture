<?php

namespace App\Services;

use App\Mail\StudentAcademicAppealReceived;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;

/**
 * Academic appeals, as Service Desk tickets.
 *
 * An appeal is read in full by whoever picks it up, so the ticket carries the
 * student's own words rather than a summary of them. The grounds come first:
 * they decide whether the appeal is admissible at all under the policy.
 */
class AcademicAppealTicket extends PortalFormTicket
{
    protected function configKey(): string
    {
        return 'academic_appeal';
    }

    protected function referencePrefix(): string
    {
        return 'AAF';
    }

    protected function receipt(Model $row, Student $student): Mailable
    {
        return new StudentAcademicAppealReceived(
            appeal: $row->load('documents'),
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
            'Academic appeal (Stage 1) — '.($student->registration_no ?: $student->id)
                .' — '.$row->groundsLabel(),
            0,
            200
        );
    }

    protected function body(Model $row, Student $student, array $attached = []): string
    {
        $documents = $attached !== []
            ? collect($attached)
            : $row->documents->pluck('display_file_name')->filter();

        $evidence = $row->evidence_method === 'upload'
            ? ($documents->count() ? $documents->implode(', ') : 'Said they would upload, but nothing arrived')
            : $row->evidenceMethodLabel();

        $lines = [
            'Raised from the student portal (Academic Appeal Form Stage 1).',
            '',
            'Student:             '.$this->studentName($student).' ('.($student->registration_no ?: 'no registration number').')',
            'Student email:       '.($this->studentEmail($student) ?: 'Not recorded'),
            'Student mobile:      '.($student->contact?->mobile ?: 'Not recorded'),
            'Programme / course:  '.($row->course_name ?: 'Not recorded'),
            '',
            'Formal notification received: '.($row->notified === 'yes' ? 'Yes' : 'No'),
            'Grounds for appeal:  '.$row->groundsLabel(),
            '',
            'Attempts at early resolution:',
            trim((string) $row->early_resolution),
            '',
            'Decision being appealed:',
            trim((string) $row->decision),
            '',
            'Outcome sought:',
            trim((string) $row->outcome),
            '',
            'Supporting evidence: '.$evidence,
            'What they say they are providing:',
            trim((string) $row->evidence_statement),
            '',
            'Declaration:         '.($row->declaration_accepted
                ? 'Confirmed by '.($row->declaration_name ?: $this->studentName($student))
                    .' on '.optional($row->created_at)->format('j F Y, g:ia')
                : 'Not confirmed'),
            'Submitted from:      '.$this->device($row),
            'IP address:          '.($row->ip_address ?: 'Not recorded'),
            '',
            'Portal request reference: AAF-'.$row->id,
        ];

        return implode("\n", $lines);
    }

    private function device(Model $row): string
    {
        $parts = array_filter([$row->device_type, $row->device_os, $row->device_browser, $row->device_screen]);

        return $parts ? implode(' · ', $parts) : 'Not recorded';
    }
}
