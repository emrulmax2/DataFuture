<?php

namespace App\Services\SlcRegistration;

/**
 * The prototype's checks, run on the server: SLC's line against what we hold (runChecks), the
 * suggested action for each difference (suggestRoute), Identify's candidate score
 * (scoreCandidate) and the Pre-confirm awarding body and attendance checks.
 *
 * Where the real data differs from the prototype's sample:
 *   - the course is matched on SLC course codes (Course Creation venue codes), not a separate map;
 *   - our year is the position of the upload year's instance among the course's instances, and
 *     it cannot be "updated" (it follows from the course), so a year difference is CoC or No action;
 *   - SLC's address is compared with the permanent and the term-time address (either matches);
 *   - SLC's first name also matches our first name when ours holds the middle names too;
 *   - the awarding body check is "has a current registration on this course" — the app does not
 *     hold which Pearson code each course should have.
 */
class Checks
{
    public const FIELD_LABEL = ['title' => 'Title', 'first' => 'First name', 'last' => 'Last name', 'gender' => 'Gender', 'dob' => 'Date of birth', 'course' => 'Course code', 'courseName' => 'Course name', 'year' => 'Year', 'start' => 'Start date', 'address' => 'Address', 'postcode' => 'Postcode', 'ssn' => 'SSN', 'tfl' => 'TFL amount'];

    public const FIELD_ROUTES = [
        'title' => ['update', 'contact', 'accept'], 'first' => ['update', 'contact'], 'last' => ['update', 'contact'], 'gender' => ['update', 'contact'],
        'dob' => ['update', 'contact'], 'course' => ['coc'], 'courseName' => ['coc', 'accept'], 'year' => ['coc', 'accept'], 'start' => ['coc'],
        'address' => ['update', 'contact', 'accept'], 'postcode' => ['update', 'contact'], 'tfl' => ['coc', 'contact'],
    ];

    public const COC_TYPE = ['course' => 'Transfer – course', 'courseName' => 'Transfer – course', 'start' => 'Transfer – start date', 'year' => 'Transfer – course year', 'tfl' => 'Fee revision'];

    /** slc_cocs.coc_type for each */
    public const COC_ENUM = ['course' => 'Transfer', 'courseName' => 'Transfer', 'start' => 'Transfer', 'year' => 'Transfer', 'tfl' => 'Fee'];

    private const NAME_STOPWORDS = ['HND', 'HNC', 'IN', 'AND', 'OF', 'THE', 'FOR', 'LEVEL', 'BTEC', 'PEARSON', 'RQF', 'DIPLOMA', 'CERTIFICATE', 'HIGHER', 'NATIONAL'];

    /**
     * @param array $r   SLC's fields (SisFile::FIELDS)
     * @param array $s   StudentSnapshots row
     * @param array $codeMap  SLC code => course creation venues
     */
    public static function run(array $r, array $s, array $codeMap, string $yearName = ''): array
    {
        $out = [];
        $c = function ($key, $slc, $ours, $ok, $note = '', $extra = []) use (&$out) {
            $out[] = ['key' => $key, 'label' => self::FIELD_LABEL[$key], 'slc' => (string) $slc, 'ours' => (string) $ours, 'ok' => (bool) $ok, 'note' => $note] + $extra;
        };
        $eqU = fn ($a, $b) => Text::norm($a) === Text::norm($b);

        $c('title', $r['title'] ?: '–', $s['title'] ?: '–', $eqU($r['title'], $s['title']));
        $c('first', $r['first'], $s['first'], Text::nameSim($r['first'], $s['first']) == 1 || ($r['other'] !== '' && Text::nameSim($r['first'].' '.$r['other'], $s['first']) == 1) || self::firstNameMatch($r['first'], $r['other'], $s['first']));
        $c('last', $r['last'], $s['last'], Text::nameSim($r['last'], $s['last']) == 1);
        $c('gender', $r['gender'], Text::genderWord($s['gender']), $r['gender'] === $s['gender']);
        $dIso = Text::slcToIso($r['dob']);
        $c('dob', Text::fmtSlc($r['dob']), Text::fmtIso($s['dob']), $dIso !== '' && $dIso === $s['dob'], Text::swapDM($dIso) === $s['dob'] && $s['dob'] !== '' ? 'Day and month look swapped' : 'Check ID documents');

        $code = strtoupper($r['slcCode']);
        $mapped = self::mappedEntry($codeMap, $code, $s['course_creation_id']);
        $courseMismatch = !$mapped || $s['slc_code'] !== $code;
        $ourCourse = trim(($s['intake'] ?: '–').' · '.($s['course_name'] ?: 'No active course'));
        $c('course', $r['slcCode'].($mapped ? ' → '.$mapped['label'] : ' (not mapped)').' · '.$r['courseName'], $ourCourse, !$courseMismatch,
            $mapped ? ($s['slc_code'] !== '' ? 'Different course – one CoC covers code and name' : 'No SLC code on the student’s course – add it in Course mapping') : 'Map this SLC code in Course mapping');
        $tokens = self::nameTokens($s['course_name']);
        $nameOk = !array_diff($tokens, explode(' ', Text::norm($r['courseName'])));
        $c('courseName', $r['courseName'], $s['course_name'] ?: '–', $nameOk || $courseMismatch, $courseMismatch ? 'Covered by the course code CoC' : 'SLC truncates course names',
            ['courseMismatch' => $courseMismatch, 'covered' => $courseMismatch && !$nameOk]);

        $inst = $s['instance'];
        $c('year', $r['year'], $inst ? 'Year '.$inst['rank'] : 'No '.($yearName ?: 'instance').' instance', $inst && (int) $r['year'] === $inst['rank']);
        $c('start', Text::fmtSlc($r['start']), $inst ? Text::fmtIso($inst['start']).' ('.($s['intake'] ?: 'intake').')' : '–', $inst && Text::slcToIso($r['start']) === $inst['start'], 'Must match the intake start');

        [$addr, $aEq] = self::bestAddress($r['address'], $s['addresses']);
        $c('address', Text::titleCase($r['address']), $addr['text'] ?? '–', $aEq, Text::addrSim($r['address'], $addr['text'] ?? '') >= 0.6 ? 'Minor difference' : 'Different address');
        $pcMatch = collect($s['addresses'])->first(fn ($a) => Text::pcNorm($a['postcode']) !== '' && Text::pcNorm($a['postcode']) === Text::pcNorm($r['postcode']));
        $c('postcode', $r['postcode'], $pcMatch['postcode'] ?? ($addr['postcode'] ?? '–'), (bool) $pcMatch);
        $c('ssn', $r['ssn'], $s['ssn'] ?: '–', $s['ssn'] === $r['ssn']);

        $tfl = (float) $r['tfl'];
        $fee = $inst['fee'] ?? null;
        $c('tfl', Text::money($tfl), $fee !== null ? Text::money($fee).' (course fee)' : '–', $fee !== null && abs($tfl - $fee) < 0.005, $tfl <= 0 ? 'No Tuition Fee Loan' : 'TFL should equal the course fee', ['tflZero' => $tfl <= 0]);

        return $out;
    }

    public static function suggest(array $ch): ?string
    {
        return match ($ch['key']) {
            'tfl' => $ch['tflZero'] ? 'contact' : 'coc',
            'courseName' => $ch['courseMismatch'] ? 'coc' : 'accept',
            'course', 'start', 'year' => 'coc',
            'dob' => null, // decided after an ID check
            default => 'update',
        };
    }

    /**
     * Identify's candidate score. With $detail each chip also carries what SLC sent (slc), what we
     * hold (ours) and why it is only partial (note) — the chip's hover card.
     */
    public static function score(array $r, array $s, bool $detail = false): array
    {
        $chips = [];
        $pts = 0;
        $max = 0;
        $add = function ($label, $w, $v, ?callable $values = null) use (&$chips, &$pts, &$max, $detail) {
            $max += $w;
            $pts += $w * $v;
            $chips[] = ['label' => $label, 'st' => $v >= 1 ? 'ok' : ($v > 0 ? 'part' : 'no')] + ($detail && $values ? $values($v) : []);
        };
        $nameNote = fn ($slc, $ours, $v) => $v >= 1 ? '' : (self::wordDiff(Text::norm($slc), Text::norm($ours)) ?: ($v >= 0.6 ? 'Close spelling' : 'Different'));

        $firstOther = trim($r['first'].' '.$r['other']);
        $vFirst = Text::nameSim($r['first'], $s['first']);
        $vOther = $r['other'] !== '' ? Text::nameSim($firstOther, $s['first']) : 0;
        $add('First name', 20, self::firstNameMatch($r['first'], $r['other'], $s['first']) ? 1 : max($vFirst, $vOther), fn ($v) => [
            'slc' => $r['first'].($r['other'] !== '' ? "\nOther names: ".$r['other'] : ''), 'ours' => $s['first'],
            'note' => $nameNote($vOther > $vFirst ? $firstOther : $r['first'], $s['first'], $v),
        ]);
        $add('Last name', 25, Text::nameSim($r['last'], $s['last']), fn ($v) => ['slc' => $r['last'], 'ours' => $s['last'], 'note' => $nameNote($r['last'], $s['last'], $v)]);
        $d = Text::slcToIso($r['dob']);
        $add('DOB', 25, $d !== '' && $d === $s['dob'] ? 1 : (Text::swapDM($d) === $s['dob'] && $s['dob'] !== '' ? 0.4 : (substr($d, 0, 4) === substr($s['dob'], 0, 4) && $d !== '' ? 0.2 : 0)), fn ($v) => [
            'slc' => Text::fmtSlc($r['dob']), 'ours' => Text::fmtIso($s['dob']),
            'note' => $v >= 1 ? '' : ($v >= 0.4 ? 'Day and month look swapped' : ($v > 0 ? 'Same year of birth only' : 'Different date')),
        ]);
        $add('Gender', 5, $r['gender'] === $s['gender'] ? 1 : 0, fn ($v) => ['slc' => Text::genderWord($r['gender']), 'ours' => Text::genderWord($s['gender']), 'note' => '']);
        $pcBest = collect($s['addresses'])->sortByDesc(fn ($a) => Text::pcSim($r['postcode'], $a['postcode']))->first();
        $add('Postcode', 15, $pcBest ? Text::pcSim($r['postcode'], $pcBest['postcode']) : 0, fn ($v) => [
            'slc' => $r['postcode'], 'ours' => $pcBest['postcode'] ?? '',
            'note' => $v >= 1 ? '' : ($v > 0 ? 'Same postcode area, different street' : ($pcBest ? 'Different postcode' : 'No address on our record')),
        ]);
        [$addr, $exact] = self::bestAddress($r['address'], $s['addresses']);
        $add('Address', 5, $exact ? 1 : (collect($s['addresses'])->map(fn ($a) => Text::addrSim($r['address'], $a['text']))->max() ?? 0), fn ($v) => [
            'slc' => Text::titleCase($r['address']), 'ours' => self::addressLines($s['addresses']),
            'note' => $v >= 1 ? '' : ($addr ? (self::wordDiff(Text::addrNorm($r['address']), Text::addrNorm($addr['text'])) ?: 'Different address') : 'No address on our record'),
        ]);
        $add('Course', 5, $s['slc_code'] !== '' && $s['slc_code'] === strtoupper($r['slcCode']) ? 1 : 0, fn ($v) => [
            'slc' => trim($r['slcCode'].' · '.$r['courseName'], ' ·'),
            'ours' => trim(($s['slc_code'] ?: 'No SLC code').' · '.($s['course_name'] ?: 'No active course'), ' ·'),
            'note' => $v >= 1 ? '' : ($s['slc_code'] !== '' ? 'Different SLC course code' : 'Their course has no SLC code'),
        ]);

        return ['pct' => (int) round($pts / $max * 100), 'chips' => $chips];
    }

    /**
     * Our first-name field often holds the middle names too ("Genowefa Natalia") where SLC splits
     * them into first and other names: a match when SLC's first name is in ours and every word of
     * ours is among SLC's first and other names.
     */
    public static function firstNameMatch(string $first, string $other, string $ours): bool
    {
        $words = fn ($v) => array_values(array_filter(explode(' ', Text::norm($v)), 'strlen'));
        $f = $words($first);
        $u = $words($ours);

        return $f && $u && !array_diff($f, $u) && !array_diff($u, array_merge($f, $words($other)));
    }

    /** "Only SLC has: Essex · Only we have: Flat 2" — the words one side has and the other lacks. */
    private static function wordDiff(string $slc, string $ours): string
    {
        $a = array_values(array_unique(array_filter(explode(' ', $slc), 'strlen')));
        $b = array_values(array_unique(array_filter(explode(' ', $ours), 'strlen')));
        $onlySlc = array_diff($a, $b);
        $onlyOurs = array_diff($b, $a);
        $parts = [];
        if ($onlySlc) {
            $parts[] = 'Only SLC has: '.Text::titleCase(implode(' ', $onlySlc));
        }
        if ($onlyOurs) {
            $parts[] = 'Only we have: '.Text::titleCase(implode(' ', $onlyOurs));
        }

        return implode(' · ', $parts);
    }

    /** Our address(es) for the hover card: one line, or "Permanent: … / Term-time: …" when they differ. */
    private static function addressLines(array $addresses): string
    {
        if (!$addresses) {
            return '';
        }
        if (count($addresses) === 1) {
            return $addresses[0]['text'].', '.self::nbsp($addresses[0]['postcode']);
        }

        return implode("\n", array_map(fn ($a) => ($a['type'] === 'permanent' ? 'Permanent' : 'Term-time').': '.$a['text'].', '.self::nbsp($a['postcode']), $addresses));
    }

    /** A postcode that does not break across lines. */
    private static function nbsp(string $postcode): string
    {
        return str_replace(' ', "\u{00A0}", $postcode);
    }

    public static function awardingBody(array $s): array
    {
        $b = $s['abody'];
        $ab = $b ? ['qual' => trim(($s['awarding_body'] ?: 'Awarding body').($b['course_code'] && strtoupper($b['course_code']) !== 'N/A' ? ' · '.$b['course_code'] : '')), 'regNo' => $b['reference'], 'regDate' => $b['registration_date']] : null;
        if (!$b || $b['reference'] === '' || in_array(strtoupper($b['reference']), ['N/A', 'NA'], true)) {
            return ['st' => 'bad', 'text' => 'No awarding body registration found', 'ab' => null, 'expected' => ($s['awarding_body'] ?: 'Awarding body').' registration on '.($s['course_name'] ?: 'the course')];
        }
        if ($b['expire_date'] && $b['expire_date'] < now()->toDateString()) {
            return ['st' => 'bad', 'text' => 'Awarding body registration expired '.Text::fmtIso($b['expire_date']), 'ab' => $ab, 'expected' => null];
        }

        return ['st' => 'ok', 'text' => 'Registered with the awarding body', 'ab' => $ab, 'expected' => null];
    }

    public static function attendance(array $s): array
    {
        $a = $s['att'];
        $pct = $a['held'] ? (int) round($a['attended'] / $a['held'] * 100) : 0;
        $lastAgo = $a['last'] ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($a['last'])->startOfDay()) : null;
        $base = ['pct' => $pct, 'last' => $a['last'], 'a' => ['held' => $a['held'], 'attended' => $a['attended'], 'lastAgo' => $lastAgo, 'physical' => $a['physical'], 'online' => $a['online'], 'other' => $a['other'], 'otherNames' => $a['otherNames']]];
        if (!$a['attended']) {
            return ['st' => 'bad', 'text' => 'No attendance since course start'] + $base;
        }
        if ($lastAgo > 14) {
            return ['st' => 'warn', 'text' => 'Not attended for '.$lastAgo.' days'] + $base;
        }
        if ($pct < 50) {
            return ['st' => 'warn', 'text' => 'Low attendance'] + $base;
        }

        return ['st' => 'ok', 'text' => 'Attending'] + $base;
    }

    /** The venue row a code maps to, preferring the student's own course creation. */
    public static function mappedEntry(array $codeMap, string $code, ?int $courseCreationId): ?array
    {
        $list = $codeMap[$code] ?? [];
        foreach ($list as $e) {
            if ($courseCreationId && $e['course_creation_id'] === $courseCreationId) {
                return $e;
            }
        }

        return $list[0] ?? null;
    }

    /** [the address shown, is it an exact match] — the exact one, else the closest. */
    public static function bestAddress(string $slc, array $addresses): array
    {
        $best = null;
        $score = -1;
        foreach ($addresses as $a) {
            if (Text::addrNorm($a['text']) === Text::addrNorm($slc)) {
                return [$a, true];
            }
            $sim = Text::addrSim($slc, $a['text']);
            if ($sim > $score) {
                $best = $a;
                $score = $sim;
            }
        }

        return [$best, false];
    }

    /** The words of our course name SLC's (truncated) name must contain. */
    private static function nameTokens(string $name): array
    {
        $main = trim(explode('(', $name)[0]);

        return array_values(array_filter(explode(' ', Text::norm($main)), fn ($t) => $t !== '' && !in_array($t, self::NAME_STOPWORDS, true) && !ctype_digit($t)));
    }
}
