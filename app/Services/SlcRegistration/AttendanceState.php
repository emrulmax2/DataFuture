<?php

namespace App\Services\SlcRegistration;

use App\Models\SlcAttendanceImport;
use App\Models\SlcAttendanceImportRow;
use Illuminate\Support\Facades\DB;

/**
 * The Bulk attendance part of the /slc-registration payload (steps 1–2): the current upload (the
 * newest), each line with the student it matched — their course, year and SLC registration for the
 * year next to SLC's — and, for a line with no match, the likely students (name, date of birth,
 * course) to give the SSN to.
 */
class AttendanceState
{
    public function build(): array
    {
        $import = SlcAttendanceImport::with('academicYear')->latest('id')->first();
        if (!$import) {
            return ['import' => null, 'rows' => [], 'codes' => self::codes(), 'batches' => $this->batches(\App\Models\SlcAttendanceBatch::all()->keyBy('id')), 'counts' => ['all' => 0, 'matched' => 0, 'ssn' => 0, 'linked' => 0, 'unmatched' => 0, 'data' => 0, 'checked' => 0, 'withErrors' => 0, 'clean' => 0, 'errors' => 0,
                'attending' => 0, 'noAttendance' => 0, 'codeReady' => 0, 'codeBlocked' => 0, 'codeNoReg' => 0, 'confirmed' => 0, 'cocQueued' => 0, 'cocToSubmit' => 0, 'awaiting' => 0]];
        }
        $rows = SlcAttendanceImportRow::with('fields')->where('slc_attendance_import_id', $import->id)->orderBy('line_no')->get();
        $yearId = $import->academic_year_id;
        $yearName = (string) ($import->academicYear?->name ?? '');
        $students = StudentSnapshots::load($rows->pluck('student_id')->filter()->all(), $yearId);
        // the agreement of each student's registration for the year (Data check: fee and TFL)
        $regIds = collect($students)->pluck('registration.id')->filter()->all();
        $agreements = $regIds ? DB::table('slc_agreements')->whereIn('slc_registration_id', $regIds)->whereNull('deleted_at')->orderBy('id')
            ->get(['slc_registration_id', 'fees', 'commission_amount', 'discount', 'total', 'slc_coursecode'])->keyBy('slc_registration_id') : collect();
        $pool = [];
        if ($rows->contains(fn ($r) => !$r->student_id)) {
            $poolIds = DB::table('students')->whereNull('deleted_at')->where(fn ($q) => $q->whereNull('ssn_no')->orWhere('ssn_no', ''))->pluck('id')->all();
            $pool = StudentSnapshots::load($poolIds, $yearId);
        }
        $names = RegistrationState::names(array_merge($rows->pluck('linked_by')->all(), $rows->pluck('code_by')->all(), $rows->pluck('confirmed_by')->all(), [$import->created_by]));
        // steps 4–6: the register in each student's liability period, their status, the batches and queued CoCs
        $liab = $this->liability($students, $rows);
        $statusOf = $rows->pluck('student_id')->filter()->isEmpty() ? collect() : DB::table('students as s')->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
            ->whereIn('s.id', $rows->pluck('student_id')->filter())->pluck('st.name', 's.id');
        $batchById = \App\Models\SlcAttendanceBatch::all()->keyBy('id');
        $cocs = DB::table('slc_cocs')->whereIn('id', $rows->pluck('slc_coc_id')->filter())->get(['id', 'coc_type', 'actioned', 'deleted_at'])->keyBy('id');

        $out = [];
        foreach ($rows as $row) {
            $r = $row->data;
            $s = $students[$row->student_id] ?? null;
            $cands = [];
            if (!$s) {
                foreach ($pool as $p) {
                    $sc = self::score($r, $p);
                    if ($sc['pct'] >= 40) {
                        $cands[] = ['p' => $p, 'pct' => $sc['pct'], 'chips' => $sc['chips']];
                    }
                }
                usort($cands, fn ($a, $b) => $b['pct'] <=> $a['pct']);
                $cands = array_map(fn ($c) => ['s' => self::studentOut($c['p']), 'pct' => $c['pct'], 'chips' => $c['chips']], array_slice($cands, 0, 3));
            }
            // Data check: SLC's line against our records, with what was decided for each difference
            $checks = [];
            if ($s) {
                $agr = $s['registration'] ? $agreements->get($s['registration']['id']) : null;
                $routes = $row->fields->keyBy('field');
                foreach (AttendanceChecks::run($r, $s, $agr ? (array) $agr : null, $yearName) as $ch) {
                    $f = $ch['ok'] ? null : $routes->get($ch['key']);
                    $ch['route'] = $f?->route;
                    $ch['sugg'] = $ch['ok'] ? null : AttendanceChecks::suggest($ch);
                    $checks[] = $ch;
                }
            }
            $unrouted = count(array_filter($checks, fn ($c) => !$c['ok'] && !$c['route']));
            // codes (step 5): a missing registration does not hold a code back; other open differences do
            $dataOpen = count(array_filter($checks, fn ($c) => !$c['ok'] && !$c['route'] && $c['key'] !== 'registration'));
            $l = $s ? ($liab[$s['id']] ?? null) : null;
            $sugg = $s && $l ? AttendanceCodes::suggest((string) ($statusOf[$s['id']] ?? ''), $l, $checks) : null;
            $batch = $batchById->get($row->slc_attendance_batch_id ?: $row->earlier_batch_id);
            $coc = $row->slc_coc_id ? $cocs->get($row->slc_coc_id) : null;
            $final = $row->attendance_code ?: ($sugg['code'] ?? null);
            $out[] = [
                'id' => $row->id, 'line' => $row->line_no, 'r' => $r,
                'link' => $row->student_id ? ['method' => $row->match_method, 'by' => $row->linked_by ? ($names[$row->linked_by] ?? '') : ''] : null,
                's' => $s ? self::studentOut($s) + [
                    // SLC's course code and year against ours, for a quick look before attendance is confirmed
                    'courseOk' => $s['slc_code'] !== '' && $s['slc_code'] === strtoupper($r['slcCode'] ?? ''),
                    'yearOk' => $s['instance'] && (int) $s['instance']['rank'] === (int) ($r['year'] ?? 0),
                    // the register since the start of their current year of study, as Pre-confirm shows it
                    'att' => Checks::attendance($s),
                    'fee' => $s['instance']['fee'] ?? null, 'slcUrl' => route('student.slc.history', $s['id']),
                ] : null,
                'cands' => $cands,
                'checks' => $checks,
                // data = differences still to decide; checked = none, or all decided
                'stage' => !$s ? 'match' : ($unrouted ? 'data' : 'checked'),
                'status' => $s ? (string) ($statusOf[$s['id']] ?? '') : '',
                'liab' => $l,
                'code' => $s ? [
                    'suggested' => $sugg['code'] ?? null, 'reason' => $sugg['reason'] ?? '', 'review' => $sugg['review'] ?? false,
                    'chosen' => $row->attendance_code, 'chosenBy' => $row->code_by ? ($names[$row->code_by] ?? '') : '', 'chosenAt' => $row->code_at?->format('d/m H:i'),
                    'final' => $final, 'coc' => AttendanceCodes::needsCoc($final),
                ] : null,
                'confirm' => $row->confirmed_code ? [
                    'code' => $row->confirmed_code, 'by' => $row->earlier_batch_id ? 'Earlier batch' : ($names[$row->confirmed_by] ?? ''), 'at' => $row->confirmed_at?->format('d/m H:i'),
                    'batchId' => $batch?->code, 'batchStatus' => $batch?->status, 'earlier' => (bool) $row->earlier_batch_id,
                ] : null,
                'coc' => $coc && !$coc->deleted_at ? ['id' => (int) $coc->id, 'type' => $coc->coc_type, 'actioned' => $coc->actioned === 'Yes'] : null,
                // step 5 (user, 2 Nov 2026: no SLC registration → the code can't be submitted): noreg = no SLC
                // registration recorded for the year; blocked = Data check differences to decide; ready = can be
                // confirmed; confirmed = in a batch
                'codeStage' => !$s ? null : ($row->confirmed_code ? 'confirmed' : (!$s['registration'] ? 'noreg' : ($dataOpen ? 'blocked' : ($final ? 'ready' : 'nocode')))),
                'dataOpen' => $s ? $dataOpen : 0,
            ];
        }
        $matched = count(array_filter($out, fn ($i) => $i['s']));
        $withErrors = array_filter($out, fn ($i) => array_filter($i['checks'], fn ($c) => !$c['ok']));

        $matchedRows = array_filter($out, fn ($i) => $i['s']);
        $batchesOut = $this->batches($batchById);

        return [
            'codes' => self::codes(),
            'batches' => $batchesOut,
            'import' => [
                'id' => $import->id, 'fileName' => $import->file_name, 'header' => $import->header, 'hei' => $import->hei, 'heiName' => $import->hei_name,
                'reportDate' => $import->report_date?->toDateString(), 'term' => $import->term, 'termName' => implode(', ', array_map([AttendanceFile::class, 'termName'], explode(',', (string) $import->term))),
                'year' => $import->academicYear?->name, 'totals' => $import->totals ?? [], 'errors' => $import->error_lines ?? [],
                'count' => $import->row_count, 'at' => $import->created_at?->format('d/m/Y H:i'), 'by' => $names[$import->created_by] ?? '',
            ],
            'rows' => $out,
            'counts' => [
                'all' => count($out), 'matched' => $matched, 'unmatched' => count($out) - $matched,
                'ssn' => count(array_filter($out, fn ($i) => ($i['link']['method'] ?? null) === 'ssn')),
                'linked' => count(array_filter($out, fn ($i) => ($i['link']['method'] ?? null) === 'linked')),
                'data' => count(array_filter($out, fn ($i) => $i['stage'] === 'data')),
                'checked' => count(array_filter($out, fn ($i) => $i['stage'] === 'checked')),
                'withErrors' => count($withErrors), 'clean' => $matched - count($withErrors),
                'errors' => array_sum(array_map(fn ($i) => count(array_filter($i['checks'], fn ($c) => !$c['ok'])), $out)),
                'attending' => count(array_filter($matchedRows, fn ($i) => ($i['liab']['a']['attended'] ?? 0) > 0)),
                'noAttendance' => count(array_filter($matchedRows, fn ($i) => $i['liab'] && $i['liab']['started'] && !$i['liab']['a']['attended'])),
                'codeReady' => count(array_filter($matchedRows, fn ($i) => $i['codeStage'] === 'ready')),
                'codeBlocked' => count(array_filter($matchedRows, fn ($i) => in_array($i['codeStage'], ['blocked', 'nocode', 'noreg'], true))),
                'codeNoReg' => count(array_filter($matchedRows, fn ($i) => $i['codeStage'] === 'noreg')),
                'confirmed' => count(array_filter($matchedRows, fn ($i) => $i['codeStage'] === 'confirmed')),
                'cocQueued' => count(array_filter($out, fn ($i) => $i['coc'])),
                'cocToSubmit' => count(array_filter($out, fn ($i) => $i['coc'] && !$i['coc']['actioned'])),
                'awaiting' => array_sum(array_map(fn ($b) => $b['status'] === 'awaiting' ? count($b['items']) : 0, $batchesOut)),
            ],
        ];
    }

    /**
     * Step 4: the liability period of each matched student — the term of the line (AUT…) on their course
     * instance for the year (instance_terms, typed by its term declaration), else that term among the
     * year's terms their class plans are in — and the register inside it, up to today.
     */
    private function liability(array $students, $rows): array
    {
        $termOf = [];
        foreach ($rows as $row) {
            if ($row->student_id) {
                $termOf[$row->student_id] = strtoupper((string) ($row->data['term'] ?? 'AUT'));
            }
        }
        if (!$termOf) {
            return [];
        }
        $instIds = collect($students)->pluck('instance.id')->filter()->unique()->all();
        $iterms = $instIds ? DB::table('instance_terms as t')->join('term_declarations as d', 'd.id', '=', 't.term_declaration_id')->leftJoin('term_types as y', 'y.id', '=', 'd.term_type_id')
            ->whereIn('t.course_creation_instance_id', $instIds)->whereNull('t.deleted_at')
            ->get(['t.course_creation_instance_id', 't.start_date', 't.end_date', 't.term_declaration_id', 't.session_term', 'y.code', 'd.name as decl']) : collect();
        $periods = [];
        $missing = [];
        foreach ($termOf as $sid => $code) {
            $s = $students[$sid] ?? null;
            $t = $s && $s['instance'] ? $iterms->first(fn ($x) => (int) $x->course_creation_instance_id === (int) $s['instance']['id'] && strtoupper((string) $x->code) === $code) : null;
            if ($t) {
                $periods[$sid] = ['start' => substr((string) $t->start_date, 0, 10), 'end' => substr((string) $t->end_date, 0, 10), 'decl' => $t->decl, 'termDeclarationId' => (int) $t->term_declaration_id, 'sessionTerm' => (int) $t->session_term, 'from' => 'instance'];
            } else {
                $missing[] = $sid;
            }
        }
        if ($missing) {
            $planTerms = DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')->join('term_declarations as d', 'd.id', '=', 'p.term_declaration_id')->leftJoin('term_types as y', 'y.id', '=', 'd.term_type_id')
                ->whereIn('a.student_id', $missing)->whereNull('a.deleted_at')->whereNull('p.deleted_at')
                ->select('a.student_id', 'd.id', 'd.name', 'd.start_date', 'd.end_date', 'y.code')->distinct()->orderBy('d.start_date')->get()->groupBy('student_id');
            foreach ($missing as $sid) {
                $d = collect($planTerms->get($sid, []))->filter(fn ($x) => strtoupper((string) $x->code) === $termOf[$sid])->last();
                if ($d) {
                    $periods[$sid] = ['start' => substr((string) $d->start_date, 0, 10), 'end' => substr((string) $d->end_date, 0, 10), 'decl' => $d->name, 'termDeclarationId' => (int) $d->id, 'sessionTerm' => null, 'from' => 'plans'];
                }
            }
        }
        $today = now()->toDateString();
        $stats = [];
        foreach (collect($periods)->groupBy(fn ($p, $sid) => $p['start'].'|'.$p['end'], true) as $key => $group) {
            [$from, $to] = explode('|', $key);
            $res = DB::table('attendances as a')->join('attendance_feed_statuses as f', 'f.id', '=', 'a.attendance_feed_status_id')
                ->whereIn('a.student_id', $group->keys()->all())->whereNull('a.deleted_at')->whereBetween('a.attendance_date', [$from, min($to, $today)])
                ->groupBy('a.student_id')
                ->selectRaw("a.student_id, COUNT(*) as held, SUM(f.attendance_count) as attended, MIN(CASE WHEN f.attendance_count = 1 THEN a.attendance_date END) as first, MAX(CASE WHEN f.attendance_count = 1 THEN a.attendance_date END) as last, "
                    ."SUM(CASE WHEN f.code = 'P' THEN 1 ELSE 0 END) as physical, SUM(CASE WHEN f.code = 'O' THEN 1 ELSE 0 END) as online, "
                    ."GROUP_CONCAT(DISTINCT CASE WHEN f.attendance_count = 1 AND f.code NOT IN ('P', 'O') THEN f.name END ORDER BY f.name SEPARATOR ', ') as other_names")
                ->get()->keyBy('student_id');
            foreach ($group->keys() as $sid) {
                $stats[$sid] = $res->get($sid);
            }
        }
        $out = [];
        foreach ($termOf as $sid => $code) {
            $p = $periods[$sid] ?? null;
            $a = $stats[$sid] ?? null;
            $held = (int) ($a->held ?? 0);
            $attended = (int) ($a->attended ?? 0);
            $last = $a && $a->last ? substr((string) $a->last, 0, 10) : null;
            $pct = $held ? (int) round($attended / $held * 100) : 0;
            $lastAgo = $last ? (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($last)->startOfDay()) : null;
            $term = AttendanceFile::termName($code);
            [$st, $text] = !$p ? ['bad', 'No '.$term.' term found for the course'] : (!$attended ? ['bad', 'No attendance in the '.$term.' term'] : ($lastAgo > 14 ? ['warn', 'Not attended for '.$lastAgo.' days'] : ($pct < 50 ? ['warn', 'Low attendance'] : ['ok', 'Attending'])));
            $out[$sid] = [
                'term' => $code, 'termName' => $term, 'decl' => $p['decl'] ?? null, 'start' => $p['start'] ?? null, 'end' => $p['end'] ?? null, 'from' => $p['from'] ?? null,
                'termDeclarationId' => $p['termDeclarationId'] ?? null, 'sessionTerm' => $p['sessionTerm'] ?? null, 'started' => $p && $p['start'] <= $today,
                'st' => $st, 'text' => $text, 'pct' => $pct, 'last' => $last, 'first' => $a && $a->first ? substr((string) $a->first, 0, 10) : null,
                'a' => ['held' => $held, 'attended' => $attended, 'lastAgo' => $lastAgo, 'physical' => (int) ($a->physical ?? 0), 'online' => (int) ($a->online ?? 0),
                    'other' => max(0, $attended - (int) ($a->physical ?? 0) - (int) ($a->online ?? 0)), 'otherNames' => (string) ($a->other_names ?? '')],
            ];
        }

        return $out;
    }

    /** Step 7: every attendance batch with its students (any upload). */
    private function batches($batchById): array
    {
        if ($batchById->isEmpty()) {
            return [];
        }
        $items = SlcAttendanceImportRow::whereIn('slc_attendance_batch_id', $batchById->keys())->orderBy('confirmed_at')->orderBy('id')
            ->get(['id', 'slc_attendance_batch_id', 'ssn', 'student_id', 'confirmed_code', 'confirmed_by', 'confirmed_at', 'data']);
        $students = DB::table('students')->whereIn('id', $items->pluck('student_id')->filter())->get(['id', 'first_name', 'last_name', 'registration_no'])->keyBy('id');
        $names = RegistrationState::names(array_merge($items->pluck('confirmed_by')->all(), $batchById->flatMap(fn ($b) => [$b->created_by, $b->downloaded_by, $b->uploaded_by])->all()));
        $when = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('d/m H:i') : '';

        return $batchById->sortBy('id')->values()->map(fn ($b) => [
            'id' => $b->code, 'status' => $b->status, 'term' => $b->term, 'header' => $b->header,
            'items' => $items->where('slc_attendance_batch_id', $b->id)->values()->map(fn ($it) => [
                'rowId' => $it->id, 'ssn' => $it->ssn, 'code' => $it->confirmed_code,
                'name' => trim(($students[$it->student_id]->first_name ?? '').' '.($students[$it->student_id]->last_name ?? '')), 'sid' => $students[$it->student_id]->registration_no ?? '',
                'url' => $it->student_id ? route('student.show', $it->student_id) : null, 'slcUrl' => $it->student_id ? route('student.slc.history', $it->student_id) : null,
                'by' => $names[$it->confirmed_by] ?? '', 'at' => $when($it->confirmed_at),
            ])->all(),
            'file' => $b->path ? ['name' => $b->file_name] : null,
            'downloadedBy' => $b->downloaded_by ? ($names[$b->downloaded_by] ?? '') : null, 'downloadedAt' => $when($b->downloaded_at),
            'uploadedBy' => $b->uploaded_by ? ($names[$b->uploaded_by] ?? '') : null, 'uploadedAt' => $when($b->uploaded_at),
        ])->all();
    }

    /** The SIS attendance codes, what they mean and whether they need a CoC (attendance_codes.coc_required). */
    private static function codes(): array
    {
        return collect(AttendanceCodes::table())->map(fn ($c, $code) => ['code' => $code, 'meaning' => AttendanceCodes::MEANING[$code] ?? '', 'coc' => $c['coc'], 'cocType' => $c['coc'] ? (AttendanceCodes::COC_TYPE[$code] ?? 'Outstanding') : null])->sortKeys()->values()->all();
    }

    private static function studentOut(array $s): array
    {
        return [
            'id' => $s['reg_no'], 'sid' => $s['id'], 'title' => $s['title'], 'first' => $s['first'], 'last' => $s['last'], 'dob' => $s['dob'], 'ssn' => $s['ssn'],
            'course' => $s['course_name'], 'intake' => $s['intake'], 'slcCode' => $s['slc_code'], 'year' => $s['instance']['rank'] ?? null,
            'registration' => $s['registration'], 'url' => route('student.show', $s['id']),
            'address' => $s['addresses'][0]['text'] ?? '', 'postcode' => $s['addresses'][0]['postcode'] ?? '',
        ];
    }

    /** A likely student for an unmatched line: the attendance report carries name, date of birth and course only. */
    public static function score(array $r, array $s): array
    {
        $chips = [];
        $pts = 0;
        $max = 0;
        $add = function ($label, $w, $v) use (&$chips, &$pts, &$max) {
            $max += $w;
            $pts += $w * $v;
            $chips[] = ['label' => $label, 'st' => $v >= 1 ? 'ok' : ($v > 0 ? 'part' : 'no')];
        };
        $first = Checks::firstNameMatch($r['forename'] ?? '', '', $s['first']) ? 1 : Text::nameSim($r['forename'] ?? '', $s['first']);
        $add('First name', 30, $first);
        $add('Last name', 35, Text::nameSim($r['surname'] ?? '', $s['last']));
        $d = Text::slcToIso($r['dob'] ?? '');
        $add('DOB', 30, $d !== '' && $d === $s['dob'] ? 1 : (Text::swapDM($d) === $s['dob'] && $s['dob'] !== '' ? 0.4 : (substr($d, 0, 4) === substr($s['dob'], 0, 4) && $d !== '' ? 0.2 : 0)));
        $add('Course', 5, $s['slc_code'] !== '' && $s['slc_code'] === strtoupper($r['slcCode'] ?? '') ? 1 : 0);

        return ['pct' => (int) round($pts / $max * 100), 'chips' => $chips];
    }
}
