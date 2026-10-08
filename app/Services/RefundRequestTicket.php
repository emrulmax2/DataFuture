<?php

namespace App\Services;

use App\Mail\StudentRefundRequestReceived;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;

/**
 * Refund requests, as Service Desk tickets.
 *
 * The bank details go on the ticket in full, because that is where the refund
 * is actually processed from and a half-masked account number would send
 * somebody back here to look it up. Everywhere a student can see — the
 * confirmation page, the receipt email — shows the last four digits only.
 */
class RefundRequestTicket extends PortalFormTicket
{
    protected function configKey(): string
    {
        return 'refund';
    }

    protected function referencePrefix(): string
    {
        return 'RRF';
    }

    protected function receipt(Model $row, Student $student): Mailable
    {
        return new StudentRefundRequestReceived(
            refund: $row->load('documents'),
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
            'Refund request — '.($student->registration_no ?: $student->id)
                .' — '.$this->money($row->refund_amount),
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
            'Raised from the student portal (Refund Request Form).',
            '',
            'Student:             '.$this->studentName($student).' ('.($student->registration_no ?: 'no registration number').')',
            'Student email:       '.($this->studentEmail($student) ?: 'Not recorded'),
            'Student mobile:      '.($student->contact?->mobile ?: 'Not recorded'),
            'Course:              '.($row->course_name ?: 'Not recorded'),
            'Intake:              '.($row->intake_semester ?: 'Not recorded'),
            '',
            'Refund amount:       '.$this->money($row->refund_amount),
            '',
            'Reasons for refund:',
            trim((string) $row->reason),
            '',
            'Bank details (student\'s own account — the College pays no third party):',
            '  Account holder:    '.($row->account_holder_name ?: $this->studentName($student)),
            '  Account number:    '.($row->account_number ?: 'Not recorded'),
            '  Sort code:         '.($row->sort_code ?: 'Not recorded'),
            '',
            'Supporting documents: '.($documents->count() ? $documents->implode(', ') : 'None provided'),
            'Declaration:         '.($row->declaration_accepted
                ? 'Confirmed by '.($row->declaration_name ?: $this->studentName($student))
                    .' on '.optional($row->created_at)->format('j F Y, g:ia')
                : 'Not confirmed'),
            'Submitted from:      '.$this->device($row),
            'IP address:          '.($row->ip_address ?: 'Not recorded'),
            '',
            'Portal request reference: RRF-'.$row->id,
        ];

        return implode("\n", $lines);
    }

    private function money($amount): string
    {
        return $amount !== null ? '£'.number_format((float) $amount, 2) : 'Not recorded';
    }

    private function device(Model $row): string
    {
        $parts = array_filter([$row->device_type, $row->device_os, $row->device_browser, $row->device_screen]);

        return $parts ? implode(' · ', $parts) : 'Not recorded';
    }
}
