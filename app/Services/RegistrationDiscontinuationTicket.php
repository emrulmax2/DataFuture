<?php

namespace App\Services;

use App\Mail\StudentRegistrationDiscontinuationReceived;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;

/**
 * Registration discontinuation requests, as Service Desk tickets.
 *
 * A student leaving touches more than one office, so the ticket leads with the
 * date it takes effect and whether they have spoken to anyone here — those are
 * what decide who has to act, and how quickly.
 */
class RegistrationDiscontinuationTicket extends PortalFormTicket
{
    protected function configKey(): string
    {
        return 'discontinuation';
    }

    protected function referencePrefix(): string
    {
        return 'RDF';
    }

    protected function receipt(Model $row, Student $student): Mailable
    {
        return new StudentRegistrationDiscontinuationReceived(
            discontinuation: $row,
            studentName: $this->studentName($student),
            registrationNo: $student->registration_no,
            studentEmail: $this->studentEmail($student),
            logoPath: $this->logoPath(),
        );
    }

    protected function subject(Model $row, Student $student): string
    {
        return mb_substr(
            'Registration discontinuation — '.($student->registration_no ?: $student->id)
                .' — effective '.$this->readableDate($row->effective_from),
            0,
            200
        );
    }

    protected function body(Model $row, Student $student, array $attached = []): string
    {
        $lines = [
            'Raised from the student portal (Registration Discontinuation Form).',
            '',
            'Student:             '.$this->studentName($student).' ('.($student->registration_no ?: 'no registration number').')',
            'Student email:       '.($this->studentEmail($student) ?: 'Not recorded'),
            'Student mobile:      '.($student->contact?->mobile ?: 'Not recorded'),
            'Course:              '.($row->course_name ?: 'Not recorded'),
            '',
            'Effective from:      '.$this->readableDate($row->effective_from),
            '',
            'Reasons for discontinuation:',
            trim((string) $row->reason),
            '',
            'Discussed with LCC staff:   '.$this->discussed($row),
            'Offer from another institute: '.$this->offer($row),
            '',
            'Declaration:         '.($row->declaration_accepted
                ? 'Confirmed by '.($row->declaration_name ?: $this->studentName($student))
                    .' on '.optional($row->created_at)->format('j F Y, g:ia')
                : 'Not confirmed'),
            /* Recorded because the form tells the student it is, and because a
               withdrawal is worth being able to trace back. */
            'Submitted from:      '.$this->device($row),
            'IP address:          '.($row->ip_address ?: 'Not recorded'),
            '',
            'Portal request reference: RDF-'.$row->id,
        ];

        return implode("\n", $lines);
    }

    private function discussed(Model $row): string
    {
        if (! $row->discussed_with_staff):
            return 'Not answered';
        endif;

        return $row->discussed_with_staff === 'yes'
            ? 'Yes — '.($row->staff_details ?: 'no details given')
            : 'No';
    }

    private function offer(Model $row): string
    {
        if (! $row->offer_from_other_institute):
            return 'Not answered';
        endif;

        if ($row->offer_from_other_institute !== 'yes'):
            return 'No';
        endif;

        $parts = array_filter([
            $row->institute_name ?: 'institute not named',
            $row->institute_start_date ? 'starting '.$this->readableDate($row->institute_start_date) : null,
        ]);

        return 'Yes — '.implode(', ', $parts);
    }

    private function device(Model $row): string
    {
        $parts = array_filter([$row->device_type, $row->device_os, $row->device_browser, $row->device_screen]);

        return $parts ? implode(' · ', $parts) : 'Not recorded';
    }
}
