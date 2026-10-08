<?php

namespace App\Services;

use App\Mail\StudentItReportReceived;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;

/**
 * IT issues reported from campus, as Service Desk tickets.
 *
 * The one form here where the student picks the issue type: IT & Monitoring
 * answers for door access, accounts, equipment and much else, and which queue
 * a report belongs in is something only the student can say. The choice is
 * offered from Operations' own settings and raised back against the same id.
 */
class ItSupportTicket extends PortalFormTicket
{
    protected function configKey(): string
    {
        return 'it_support';
    }

    protected function referencePrefix(): string
    {
        return 'ITS';
    }

    /** The student's own answer, not a fixed one from config. */
    protected function issueTypeFor(Model $row, array $config): ?int
    {
        return $row->issue_type_id ? (int) $row->issue_type_id : ($config['issue_type_id'] ?? null);
    }

    protected function receipt(Model $row, Student $student): Mailable
    {
        return new StudentItReportReceived(
            report: $row->load('documents'),
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
            'IT issue — '.($student->registration_no ?: $student->id)
                .' — '.($row->issue_type_name ?: 'type not recorded')
                .($row->campus ? ' — '.$row->campus : ''),
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
            'Raised from the student portal (Report an IT issue on campus).',
            '',
            'Student:             '.$this->studentName($student).' ('.($student->registration_no ?: 'no registration number').')',
            'Student email:       '.($this->studentEmail($student) ?: 'Not recorded'),
            'Student mobile:      '.($student->contact?->mobile ?: 'Not recorded'),
            'Course:              '.($row->course_name ?: 'Not recorded'),
            '',
            'Issue type:          '.($row->issue_type_name ?: 'Not recorded'),
            'Campus:              '.($row->campus ?: 'Not recorded'),
            'Location:            '.($row->location ?: 'Not given'),
            '',
            'Description:',
            trim((string) $row->description),
            '',
            'Attachments:         '.($documents->count() ? $documents->implode(', ') : 'None provided'),
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
