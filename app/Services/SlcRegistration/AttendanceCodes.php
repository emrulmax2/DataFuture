<?php

namespace App\Services\SlcRegistration;

use Illuminate\Support\Facades\DB;

/**
 * Bulk attendance, steps 4–6 (user, 2 Nov 2026): the register in the liability period, the code it
 * suggests, and which codes need a CoC (attendance_codes.coc_required).
 *
 * Suggestion, first rule that applies:
 *   S  the student's status is a suspension            X  the status is left / terminated / discarded
 *   X  no attendance in the liability period (the term)
 *   C  Data check: SLC's course code differs            F  Data check: course fee or TFL differs
 *   A  attended in the term (flagged for a look when attendance is low or stale)
 * D, L and N are staff decisions, never suggested. Staff can choose any code.
 */
class AttendanceCodes
{
    public const MEANING = [
        'A' => 'In attendance', 'C' => 'Course mismatch – CoC', 'D' => 'Applied for the new year but not returning',
        'F' => 'In attendance, fee disputed – CoC', 'L' => 'In attendance, liability disputed, we will resolve',
        'N' => 'In attendance, liability disputed, we will not resolve – CoC', 'S' => 'Suspended – CoC', 'X' => 'Not in attendance – CoC',
    ];

    /** slc_cocs.coc_type for a code that needs a CoC */
    public const COC_TYPE = ['C' => 'Transfer', 'F' => 'Fee', 'L' => 'Outstanding', 'N' => 'Outstanding', 'S' => 'Suspension', 'X' => 'Withdrawal'];

    private const SUSPENDED = ['SUSPENDED', 'SLC SUSPENDED', 'SUSPENDED(NON-PROGRESSION)', 'SLC SUSPENDED(NON-PROGRESSION)'];
    private const GONE = ['LEFT', 'SLC LEFT', 'TERMINATED', 'SLC TERMINATED', 'DISCARDED', 'SLC DISCARDED', 'SLC DISCARDED(NON-PROGRESSION)', 'CAS WITHDRAWN', 'SPONSORSHIPWITHDRAWN', 'COURSE COMPLETED', 'SLC COURSE COMPLETED'];

    /** Codes (A…X) → [id, coc_required], from attendance_codes. */
    public static function table(): array
    {
        static $t = null;

        return $t ??= DB::table('attendance_codes')->whereNull('deleted_at')->where('active', 1)->get(['id', 'code', 'coc_required'])
            ->mapWithKeys(fn ($c) => [strtoupper($c->code) => ['id' => (int) $c->id, 'coc' => (bool) $c->coc_required]])->all();
    }

    public static function needsCoc(?string $code): bool
    {
        return $code !== null && (self::table()[strtoupper($code)]['coc'] ?? false);
    }

    /**
     * @param array  $liab   the register in the liability period (see AttendanceState)
     * @param array  $checks Data check results
     * @return array{code: ?string, reason: string, review: bool}
     */
    public static function suggest(string $status, array $liab, array $checks): array
    {
        $st = strtoupper(trim($status));
        if (in_array($st, self::SUSPENDED, true)) {
            return ['code' => 'S', 'reason' => 'Student status: '.$status, 'review' => false];
        }
        if (in_array($st, self::GONE, true)) {
            return ['code' => 'X', 'reason' => 'Student status: '.$status, 'review' => false];
        }
        if (!$liab['start']) {
            return ['code' => null, 'reason' => 'No '.$liab['termName'].' term found on the course for this year – choose a code', 'review' => true];
        }
        if (!$liab['started']) {
            return ['code' => null, 'reason' => 'The '.$liab['termName'].' term has not started', 'review' => true];
        }
        $a = $liab['a'];
        if (!$a['held']) {
            return ['code' => null, 'reason' => 'No register entries in the '.$liab['termName'].' term – check the timetable', 'review' => true];
        }
        if (!$a['attended']) {
            return ['code' => 'X', 'reason' => 'No attendance in the '.$liab['termName'].' term ('.$a['held'].' sessions held)', 'review' => false];
        }
        $bad = collect($checks)->filter(fn ($c) => !$c['ok'])->keyBy('key');
        if ($bad->has('course')) {
            return ['code' => 'C', 'reason' => 'Course code differs (Data check)', 'review' => false];
        }
        if ($bad->has('fee') || $bad->has('tfl')) {
            return ['code' => 'F', 'reason' => 'Fee or TFL differs from the agreement (Data check)', 'review' => false];
        }

        return ['code' => 'A', 'reason' => 'Attended '.$a['attended'].' of '.$a['held'].' sessions, last '.Text::fmtIso($liab['last']), 'review' => $liab['st'] !== 'ok'];
    }
}
