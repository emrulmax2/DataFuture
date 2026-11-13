<?php

namespace App\Services\SlcRegistration;

/**
 * Bulk attendance › Data check (user, 2 Nov 2026): an attendance line against our records — name,
 * date of birth, SSN, course code and registration year; whether the SLC registration for the year is
 * recorded; and, when it is, the course fee and TFL against its agreement.
 *
 *   - registration year: SLC's year of study against the registration's, or our year of study (the
 *     year's course instance) when no registration is recorded;
 *   - course fee: field 10 against the agreement's fee before the university commission (fees +
 *     commission amount); TFL: field 11 against the agreement's total.
 */
class AttendanceChecks
{
    public const FIELD_LABEL = [
        'first' => 'First name', 'last' => 'Last name', 'dob' => 'Date of birth', 'ssn' => 'SSN', 'course' => 'Course code',
        'year' => 'Registration year', 'registration' => 'SLC registration', 'fee' => 'Course fee', 'tfl' => 'TFL amount',
    ];

    public const FIELD_ROUTES = [
        'first' => ['update', 'contact', 'accept'], 'last' => ['update', 'contact', 'accept'], 'dob' => ['update', 'contact', 'accept'],
        'ssn' => ['accept'], 'course' => ['coc', 'accept'], 'year' => ['coc', 'accept'], 'registration' => ['accept'],
        'fee' => ['coc', 'accept'], 'tfl' => ['coc', 'contact', 'accept'],
    ];

    public const COC_TYPE = ['course' => 'Transfer – course', 'year' => 'Transfer – course year', 'fee' => 'Fee revision', 'tfl' => 'Fee revision'];

    public const COC_ENUM = ['course' => 'Transfer', 'year' => 'Transfer', 'fee' => 'Fee', 'tfl' => 'Fee'];

    /**
     * @param array      $r   the attendance line (AttendanceFile::FIELDS)
     * @param array      $s   StudentSnapshots row
     * @param array|null $agr the agreement of the year's registration (fees, commission_amount, total, slc_coursecode)
     */
    public static function run(array $r, array $s, ?array $agr, string $yearName): array
    {
        $out = [];
        $c = function ($key, $slc, $ours, $ok, $note = '', $extra = []) use (&$out) {
            $out[] = ['key' => $key, 'label' => self::FIELD_LABEL[$key], 'slc' => (string) $slc, 'ours' => (string) $ours, 'ok' => (bool) $ok, 'note' => $note] + $extra;
        };

        $c('first', $r['forename'], $s['first'], Text::nameSim($r['forename'], $s['first']) == 1 || self::within($r['forename'], $s['first']), 'Check ID documents');
        $c('last', $r['surname'], $s['last'], Text::nameSim($r['surname'], $s['last']) == 1 || self::within($r['surname'], $s['last']), 'Check ID documents');
        $d = Text::slcToIso($r['dob']);
        $c('dob', Text::fmtSlc($r['dob']), Text::fmtIso($s['dob']), $d !== '' && $d === $s['dob'], Text::swapDM($d) === $s['dob'] && $s['dob'] !== '' ? 'Day and month look swapped' : 'Check ID documents');
        $c('ssn', $r['ssn'], $s['ssn'] ?: '–', $s['ssn'] === $r['ssn']);

        $code = strtoupper($r['slcCode']);
        $c('course', $r['slcCode'].' · '.$r['courseName'], ($s['slc_code'] ?: 'No SLC code').' · '.($s['course_name'] ?: 'No active course'), $s['slc_code'] !== '' && $s['slc_code'] === $code,
            $s['slc_code'] === '' ? 'No SLC code on the student’s course' : 'Different course');

        $reg = $s['registration'];
        if ($reg) {
            $c('year', 'Year '.$r['year'], 'Year '.$reg['year'].' (SLC registration)', (int) $r['year'] === (int) $reg['year'], 'Registration year on the SLC tab');
        } else {
            $inst = $s['instance'];
            $c('year', 'Year '.$r['year'], $inst ? 'Year '.$inst['rank'].' (course instance)' : 'No '.$yearName.' instance', $inst && (int) $r['year'] === (int) $inst['rank'], 'No SLC registration recorded – checked against the course');
        }
        $c('registration', 'Registered in SIS', $reg ? 'Recorded '.Text::fmtIso($reg['date']).' · Year '.$reg['year'] : 'None recorded for '.$yearName, (bool) $reg,
            'Record it on the student’s SLC tab (or through Bulk registration): until then the attendance code can’t be confirmed; then fee and TFL are checked too');

        if ($reg) {
            $gross = $agr ? (float) $agr['fees'] + (float) $agr['commission_amount'] : null;
            $total = $agr ? (float) $agr['total'] : null;
            $c('fee', Text::money($r['fee']), $agr ? Text::money($gross).' (agreement)' : 'No agreement on the registration', $agr && abs((float) $r['fee'] - $gross) < 0.005, 'Course fee on the agreement');
            $tfl = (float) $r['tfl'];
            $c('tfl', Text::money($tfl), $agr ? Text::money($total).' (agreement total)' : 'No agreement on the registration', $agr && abs($tfl - $total) < 0.005,
                $tfl <= 0 ? 'No Tuition Fee Loan'.((float) ($r['tfs'] ?? $r['f13'] ?? 0) > 0 ? ' – '.Text::money($r['tfs'] ?? $r['f13']).' paid another way' : '') : 'TFL should equal the agreement total', ['tflZero' => $tfl <= 0]);
        }

        return $out;
    }

    /**
     * The attendance report has no "other names": SLC's name matches when every word of it is in ours
     * and the first words agree — "DANAIL" in "Danail Angelov", "LUMINITA" in "Luminita-Mariana",
     * "CHAVEZ" in "Chavez Quinones".
     */
    public static function within(string $slc, string $ours): bool
    {
        $a = array_values(array_filter(explode(' ', Text::norm($slc)), 'strlen'));
        $b = array_values(array_filter(explode(' ', Text::norm($ours)), 'strlen'));

        return $a && $b && $a[0] === $b[0] && !array_diff($a, $b);
    }

    /**
     * What the Data check suggests. Names get no suggestion: once middle names are allowed for, what is
     * left is usually a typo on one side ("IOUNT" for "Ionut"), decided after checking ID — as for the
     * date of birth. SSN and a missing registration are looked into, not suggested.
     */
    public static function suggest(array $ch): ?string
    {
        return match ($ch['key']) {
            'course', 'year', 'fee' => 'coc',
            'tfl' => ($ch['tflZero'] ?? false) ? 'contact' : 'coc',
            default => null,
        };
    }
}
