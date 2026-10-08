<?php

namespace App\Services;

use App\Mail\StudentMitigatingCircumstanceReceived;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;

/**
 * Mitigating circumstances claims, as Service Desk tickets.
 *
 * The assignments and their deadlines lead: the panel is deciding whether to
 * extend each one, and a claim made after a deadline reads very differently
 * from one made before it.
 */
class MitigatingCircumstanceTicket extends PortalFormTicket
{
    protected function configKey(): string
    {
        return 'mitigating_circumstances';
    }

    protected function referencePrefix(): string
    {
        return 'MCA';
    }

    protected function receipt(Model $row, Student $student): Mailable
    {
        return new StudentMitigatingCircumstanceReceived(
            claim: $row->load('assignments', 'absences', 'documents'),
            studentName: $this->studentName($student),
            registrationNo: $student->registration_no,
            studentEmail: $this->studentEmail($student),
            studentMobile: $student->contact?->mobile,
            logoPath: $this->logoPath(),
        );
    }

    protected function subject(Model $row, Student $student): string
    {
        $count = $row->isAttendance() ? $row->absences->count() : $row->assignments->count();
        $noun = $row->isAttendance() ? 'absence' : 'assignment';

        return mb_substr(
            'Mitigating circumstances ('.($row->isAttendance() ? 'attendance' : 'assignment').') — '
                .($student->registration_no ?: $student->id)
                .' — '.$count.' '.($count === 1 ? $noun : $noun.'s'),
            0,
            200
        );
    }

    /** What the claim is about: deadlines to move, or days missed. */
    protected function claimedFor(Model $row): array
    {
        if ($row->isAttendance()) {
            return [
                'Non-attendance:',
                $row->absences->isNotEmpty()
                    ? $row->absences->map(fn ($a) => '  - '.$a->period().' — '.$a->details)->implode("\n")
                    : '  - None listed',
            ];
        }

        return [
            'Assignments claimed for:',
            $row->assignments->isNotEmpty()
                ? $row->assignments->map(
                    fn ($a) => '  - '.$a->title.' (due '.$this->readableDate($a->submission_date).')'
                )->implode("\n")
                : '  - None listed',
        ];
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
            'Raised from the student portal (Mitigating Circumstances Claim Form - '
                .($row->isAttendance() ? 'Attendance' : 'Assignment').').',
            '',
            'Student:             '.$this->studentName($student).' ('.($student->registration_no ?: 'no registration number').')',
            'Student email:       '.($this->studentEmail($student) ?: 'Not recorded'),
            'Student mobile:      '.($student->contact?->mobile ?: 'Not recorded'),
            'Course:              '.($row->course_name ?: 'Not recorded'),
            'Address:             '.($row->address ?: 'Not recorded'),
            '',
            ...$this->claimedFor($row),
            '',
            'Reasons given:',
            collect($row->reasonLabels())->map(fn ($reason) => '  - '.$reason)->implode("\n") ?: '  - None given',
            '',
            'Details of the claim:',
            trim((string) $row->claim_details),
            '',
            'Supporting evidence: '.$evidence,
            '',
            'Declaration:         '.($row->declaration_accepted
                ? 'Confirmed by '.($row->declaration_name ?: $this->studentName($student))
                    .' on '.optional($row->created_at)->format('j F Y, g:ia')
                : 'Not confirmed'),
            'Submitted from:      '.$this->device($row),
            'IP address:          '.($row->ip_address ?: 'Not recorded'),
            '',
            /* The same prefix the idempotency key uses, so a ticket can be
               traced back to the row it came from. */
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
