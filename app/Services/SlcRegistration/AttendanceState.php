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
            ->get(['id', 'slc_registration_id', 'fees', 'commission_amount', 'discount', 'total', 'slc_coursecode'])->keyBy('slc_registration_id') : collect();
        $pool = [];
        if ($rows->contains(fn ($r) => !$r->student_id)) {
            $poolIds = DB::table('students')->whereNull('deleted_at')->where(fn ($q) => $q->whereNull('ssn_no')->orWhere('ssn_no', ''))->pluck('id')->all();
            $pool = StudentSnapshots::load($poolIds, $yearId);
        }
        $names = RegistrationState::names(array_merge($rows->pluck('linked_by')->all(), $rows->pluck('code_by')->all(), $rows->pluck('confirmed_by')->all(), [$import->created_by]));
        // steps 4–5 (Attendance & codes, CoCs): the register in each student's liability period, their status, the batches and queued CoCs
        $liab = $this->liability($students, $rows);
        $onTab = $this->onSlcTab($students, $liab, $agreements);
        // Not Registered for the year (registration No or N/R on the active course, no Yes): SIS takes only X or S (spec §7.2.3)
        $notRegOf = self::notRegistered($students, $yearId);
        // the SLC tab term each attendance will be recorded with (step 4's Session Term / Attendance Term, user 13 Nov 2026)
        $recTerms = $this->recordTerms($students, $liab, $notRegOf);
        // a line already recorded on the SLC tab shows the term it was recorded with
        $recordedAtt = DB::table('slc_attendances as a')->leftJoin('term_declarations as d', 'd.id', '=', 'a.term_declaration_id')
            ->whereIn('a.id', $rows->pluck('slc_attendance_id')->filter()->all() ?: [0])->get(['a.id', 'a.session_term', 'a.term_declaration_id', 'd.name'])->keyBy('id');
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
            // codes (step 4): a missing registration does not hold a code back; other open differences do
            $dataOpen = count(array_filter($checks, fn ($c) => !$c['ok'] && !$c['route'] && $c['key'] !== 'registration'));
            $l = $s ? ($liab[$s['id']] ?? null) : null;
            $sugg = $s && $l ? AttendanceCodes::suggest((string) ($statusOf[$s['id']] ?? ''), $l, $checks) : null;
            $notReg = $s && !$s['registration'] ? ($notRegOf[$s['id']] ?? null) : null;
            // the report's column 28: a code SIS already has saved or submitted for the period (spec §4.1)
            $sisCode = strtoupper(trim((string) ($r['attendanceCode'] ?? '')));
            $sisCode = $sisCode !== '' && $sisCode !== '_' ? $sisCode : null;
            if ($s && $sisCode && AttendanceCodes::importable($sisCode) && (!$notReg || in_array($sisCode, AttendanceCodes::NOT_REGISTERED, true))) {
                $sugg = ['code' => $sisCode, 'reason' => 'SIS already has '.$sisCode.' saved or submitted for this period (report column 28)', 'review' => true];
            } elseif ($notReg && !in_array($sugg['code'] ?? null, AttendanceCodes::NOT_REGISTERED, true)) {
                $sugg = ['code' => 'X', 'reason' => 'Not Registered with SLC for '.$yearName.' – SIS takes only X (not in attendance) or S (suspended)', 'review' => true];
            }
            $batch = $batchById->get($row->slc_attendance_batch_id ?: $row->earlier_batch_id);
            $coc = $row->slc_coc_id ? $cocs->get($row->slc_coc_id) : null;
            $final = $row->attendance_code ?: ($sugg['code'] ?? null);
            $out[] = [
                // what the SLC tab / Accounts already have for the liability period (user, 13 Nov 2026): confirming asks first
                'slcTab' => $s ? ($onTab[$s['id']] ?? null) : null,
                // why Bulk Attendance & Installment couldn't record it later, said now (user, 13 Nov 2026)
                'recordBlock' => $s ? self::recordBlock($s, $l, $row->confirmed_code ?: $final, (float) ($r['tfl'] ?? 0), $s['registration'] ? $agreements->get($s['registration']['id']) : null) : null,
                'id' => $row->id, 'line' => $row->line_no, 'r' => $r,
                'link' => $row->student_id ? ['method' => $row->match_method, 'by' => $row->linked_by ? ($names[$row->linked_by] ?? '') : ''] : null,
                's' => $s ? self::studentOut($s) + [
                    // SLC's course code and year against ours, for a quick look before attendance is confirmed
                    'courseOk' => $s['slc_code'] !== '' && $s['slc_code'] === strtoupper($r['slcCode'] ?? ''),
                    'yearOk' => $s['instance'] && (int) $s['instance']['rank'] === (int) ($r['year'] ?? 0),
                    // the register since the start of their current year of study, as Pre-confirm shows it
                    'att' => Checks::attendance($s),
                    'fee' => $s['instance']['fee'] ?? null, 'slcUrl' => route('student.slc.history', $s['id']), 'accountsUrl' => route('student.accounts', $s['id']),
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
                // step 4 (user, 2 Nov 2026: no SLC registration → the code can't be submitted): noreg = no SLC
                // registration recorded for the year; blocked = Data check differences to decide; ready = can be
                // confirmed; confirmed = in a batch. D (never attended) is the exception: SLC only takes it for a
                // student with no registration (spec v9.0 §7.2.3; checked on choosing and confirming it)
                // (spec §7.2.3) Not Registered: X or S only; no registration recorded: D only (user, 2 Nov 2026: no other code)
                'codeStage' => !$s ? null : ($row->confirmed_code ? 'confirmed'
                    : (!($s['registration'] || ($notReg ? in_array($final, AttendanceCodes::NOT_REGISTERED, true) : $final === 'D')) ? 'noreg'
                    : ($dataOpen ? 'blocked' : ($final ? 'ready' : 'nocode')))),
                'notReg' => $notReg,
                'sisCode' => $s ? $sisCode : null,
                'recordTerm' => $s ? self::recordTermOut($recTerms[$s['id']] ?? null, $l, $row, $row->slc_recorded ? $recordedAtt->get($row->slc_attendance_id) : null) : null,
                'sisRejected' => $row->sis_rejected_batch_id ? ['batchId' => $batchById->get($row->sis_rejected_batch_id)?->code, 'code' => $row->sis_rejected_code,
                    'reason' => (string) $row->sis_rejected_reason, 'at' => $row->sis_rejected_at?->format('d/m H:i')] : null,
                'dataOpen' => $s ? $dataOpen : 0,
            ];
        }
        $matched = count(array_filter($out, fn ($i) => $i['s']));
        $withErrors = array_filter($out, fn ($i) => array_filter($i['checks'], fn ($c) => !$c['ok']));

        $matchedRows = array_filter($out, fn ($i) => $i['s']);
        $batchesOut = $this->batches($batchById);

        return [
            'codes' => self::codes(),
            // every attendance term, for step 4's Attendance Term list (as SLC tab › Add Attendance offers them)
            'termDeclarations' => DB::table('term_declarations')->whereNull('deleted_at')->orderByDesc('id')->get(['id', 'name'])->map(fn ($t) => ['id' => (int) $t->id, 'name' => $t->name])->all(),
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
                'awaiting' => array_sum(array_map(fn ($b) => $b['status'] === 'awaiting' ? count(array_filter($b['items'], fn ($it) => !$it['rejected'])) : 0, $batchesOut)),
                'onSlcTab' => count(array_filter($matchedRows, fn ($i) => $i['slcTab'] && $i['slcTab']['attendance'] && !$i['slcTab']['recorded'])),
                'sisCode' => count(array_filter($matchedRows, fn ($i) => $i['sisCode'] && !$i['confirm'])),
                'notReg' => count(array_filter($matchedRows, fn ($i) => (bool) $i['notReg'])),
                'recordBlocked' => count(array_filter($matchedRows, fn ($i) => $i['recordBlock'])),
                // the final step: lines of batches uploaded to SIS still to record on the SLC tab, and those recorded
                'toRecord' => array_sum(array_map(fn ($b) => $b['status'] === 'uploaded' ? count(array_filter($b['items'], fn ($it) => !$it['record']['status'] && !$it['rejected'])) : 0, $batchesOut)),
                'recorded' => array_sum(array_map(fn ($b) => count(array_filter($b['items'], fn ($it) => (bool) $it['record']['status'])), $batchesOut)),
            ],
        ];
    }

    /**
     * What the SLC tab and Accounts tab already have for each student's liability period — the same records
     * Bulk Attendance & Installment would find (an attendance for the term, or the registration's session
     * term; an instalment for the term, the agreement's session term, or that attendance). "recorded": the
     * attendance is one this module recorded (an upload line points to it), not one made by hand.
     */
    private function onSlcTab(array $students, array $liab, $agreements): array
    {
        $ids = array_keys(array_filter($liab, fn ($l) => $l && $l['termDeclarationId']));
        if (!$ids) {
            return [];
        }
        $atts = DB::table('slc_attendances as a')->leftJoin('attendance_codes as c', 'c.id', '=', 'a.attendance_code_id')->whereIn('a.student_id', $ids)->whereNull('a.deleted_at')
            ->orderByDesc('a.id')->get(['a.id', 'a.student_id', 'a.student_course_relation_id', 'a.term_declaration_id', 'a.slc_registration_id', 'a.session_term', 'a.confirmation_date', 'a.created_by', 'c.code'])->groupBy('student_id');
        $insts = DB::table('slc_installments')->whereIn('student_id', $ids)->whereNull('deleted_at')->orderByDesc('id')
            ->get(['id', 'student_id', 'student_course_relation_id', 'term_declaration_id', 'slc_agreement_id', 'session_term', 'slc_attendance_id', 'installment_date', 'amount', 'slc_money_receipt_id'])->groupBy('student_id');
        $ours = SlcAttendanceImportRow::whereIn('student_id', $ids)->whereNotNull('slc_attendance_id')->pluck('slc_attendance_id')->map(fn ($v) => (int) $v)->all();
        $names = RegistrationState::names($atts->flatten()->pluck('created_by')->all());
        $day = fn ($d) => $d ? substr((string) $d, 0, 10) : null;
        $out = [];
        foreach ($ids as $sid) {
            $s = $students[$sid] ?? null;
            $l = $liab[$sid];
            if (!$s || !$s['crel_id']) {
                continue;
            }
            $regId = $s['registration']['id'] ?? null;
            $agrId = $regId ? ($agreements->get($regId)->id ?? null) : null;
            $a = collect($atts->get($sid, []))->first(fn ($x) => ((int) $x->student_course_relation_id === (int) $s['crel_id'] && (int) $x->term_declaration_id === (int) $l['termDeclarationId'])
                || ($regId && $l['sessionTerm'] && (int) $x->slc_registration_id === (int) $regId && (int) $x->session_term === (int) $l['sessionTerm']));
            $i = collect($insts->get($sid, []))->first(fn ($x) => ((int) $x->student_course_relation_id === (int) $s['crel_id'] && (int) $x->term_declaration_id === (int) $l['termDeclarationId'])
                || ($agrId && $l['sessionTerm'] && (int) $x->slc_agreement_id === (int) $agrId && (int) $x->session_term === (int) $l['sessionTerm'])
                || ($a && (int) $x->slc_attendance_id === (int) $a->id));
            if ($a || $i) {
                $out[$sid] = [
                    'attendance' => $a ? ['id' => (int) $a->id, 'code' => $a->code, 'date' => $day($a->confirmation_date), 'by' => $names[$a->created_by] ?? ''] : null,
                    'installment' => $i ? ['id' => (int) $i->id, 'amount' => (float) $i->amount, 'date' => $day($i->installment_date), 'receiptId' => $i->slc_money_receipt_id ? (int) $i->slc_money_receipt_id : null] : null,
                    'recorded' => $a && in_array((int) $a->id, $ours, true),
                ];
            }
        }

        return $out;
    }

    /**
     * For each student, the course instance's Term 1–4 (session term => attendance term) and the session terms
     * already on the SLC tab for the year — an attendance on the registration (Yes, or the Not Registered one), or
     * on the active course for one of the instance's terms — as AttendanceActions::plan finds them.
     */
    private function recordTerms(array $students, array $liab, array $notRegOf): array
    {
        $sids = array_keys(array_filter($students, fn ($s) => $s && $s['crel_id'] && $s['instance']));
        if (!$sids) {
            return [];
        }
        $iterms = DB::table('instance_terms as t')->join('term_declarations as d', 'd.id', '=', 't.term_declaration_id')
            ->whereIn('t.course_creation_instance_id', collect($sids)->map(fn ($sid) => $students[$sid]['instance']['id'])->unique()->all())
            ->whereNull('t.deleted_at')->whereNotNull('t.session_term')->orderBy('t.session_term')
            ->get(['t.course_creation_instance_id', 't.session_term', 'd.id', 'd.name'])->groupBy('course_creation_instance_id');
        $atts = DB::table('slc_attendances as a')->leftJoin('attendance_codes as c', 'c.id', '=', 'a.attendance_code_id')->whereIn('a.student_id', $sids)->whereNull('a.deleted_at')
            ->orderBy('a.id')->get(['a.id', 'a.student_id', 'a.student_course_relation_id', 'a.slc_registration_id', 'a.term_declaration_id', 'a.session_term', 'a.confirmation_date', 'c.code'])->groupBy('student_id');
        $out = [];
        foreach ($sids as $sid) {
            $s = $students[$sid];
            $terms = collect($iterms->get($s['instance']['id'], []))->mapWithKeys(fn ($t) => [(int) $t->session_term => ['id' => (int) $t->id, 'name' => $t->name]])->all();
            $l = $liab[$sid] ?? null;
            if (!$terms && $l && $l['termDeclarationId'] && $l['sessionTerm']) {
                $terms = [(int) $l['sessionTerm'] => ['id' => (int) $l['termDeclarationId'], 'name' => $l['decl']]]; // class plans
            }
            $sessionOf = collect($terms)->mapWithKeys(fn ($t, $n) => [$t['id'] => $n])->all();
            $regId = $s['registration']['id'] ?? ($notRegOf[$sid]['id'] ?? null);
            $taken = [];
            foreach (collect($atts->get($sid, []))->filter(fn ($a) => ((int) $a->student_course_relation_id === (int) $s['crel_id'] && isset($sessionOf[(int) $a->term_declaration_id]))
                || ($regId && (int) $a->slc_registration_id === (int) $regId)) as $a) {
                $n = (int) ($a->session_term ?: ($sessionOf[(int) $a->term_declaration_id] ?? 0));
                if ($n && !isset($taken[$n])) {
                    $taken[$n] = ['id' => (int) $a->id, 'code' => $a->code, 'date' => $a->confirmation_date ? substr((string) $a->confirmation_date, 0, 10) : null];
                }
            }
            $out[$sid] = ['terms' => $terms, 'taken' => $taken];
        }

        return $out;
    }

    /** A step 4 row's Session Term / Attendance Term: the instance's terms, what is suggested, chosen and so recorded. */
    private static function recordTermOut(?array $rt, ?array $l, SlcAttendanceImportRow $row, ?object $recorded = null): ?array
    {
        if (!$rt) {
            return null;
        }
        if ($recorded) { // recorded on the SLC tab: that term, no longer to choose
            $t = ['sessionTerm' => $recorded->session_term ? (int) $recorded->session_term : null, 'termDeclarationId' => $recorded->term_declaration_id ? (int) $recorded->term_declaration_id : null, 'term' => $recorded->name];

            return ['options' => collect($rt['terms'])->map(fn ($x, $n) => ['sessionTerm' => (int) $n, 'id' => $x['id'], 'name' => $x['name'], 'taken' => $rt['taken'][$n] ?? null])->values()->all(),
                'taken' => $rt['taken'], 'suggested' => $t, 'chosen' => ['sessionTerm' => $t['sessionTerm'], 'termDeclarationId' => $t['termDeclarationId']], 'final' => $t + ['chosen' => false],
                'problem' => null, 'recorded' => true, 'attendanceId' => (int) $recorded->id, 'by' => '', 'at' => null];
        }
        $sugg = RecordTerm::suggest($rt['terms'], array_keys($rt['taken']));
        $chosen = ['sessionTerm' => $row->record_session_term ? (int) $row->record_session_term : null, 'termDeclarationId' => $row->record_term_declaration_id ? (int) $row->record_term_declaration_id : null];
        $final = RecordTerm::resolve($rt['terms'], $sugg, $chosen['sessionTerm'], $chosen['termDeclarationId']);
        $problem = !$final['sessionTerm'] ? 'Terms 1–3 are already on the SLC tab'
            : (isset($rt['taken'][$final['sessionTerm']]) ? 'Term '.$final['sessionTerm'].' is already on the SLC tab (#'.$rt['taken'][$final['sessionTerm']]['id'].') – it won’t be overwritten'
            : (!$final['termDeclarationId'] ? 'No Term '.$final['sessionTerm'].' on the course instance – choose the Attendance Term' : null));

        return [
            'options' => collect($rt['terms'])->map(fn ($t, $n) => ['sessionTerm' => (int) $n, 'id' => $t['id'], 'name' => $t['name'], 'taken' => $rt['taken'][$n] ?? null])->values()->all(),
            'taken' => $rt['taken'], 'suggested' => $sugg, 'chosen' => $chosen, 'final' => $final, 'problem' => $problem,
            'recorded' => (bool) $row->slc_recorded,
            'by' => $row->record_term_by ? (RegistrationState::names([$row->record_term_by])[$row->record_term_by] ?? '') : '', 'at' => $row->record_term_at?->format('d/m H:i'),
        ];
    }

    /**
     * Students recorded as Not Registered for the year — an SLC registration No or N/R (statuses 2, 4) on their
     * active course and no Yes: SIS takes only X or S for them (spec v9.0 §7.2.3). [student id => [id, year, status]]
     */
    public static function notRegistered(array $students, ?int $yearId): array
    {
        $ids = array_keys(array_filter($students, fn ($s) => $s && !$s['registration'] && $s['crel_id']));
        if (!$ids || !$yearId) {
            return [];
        }
        $out = [];
        foreach (DB::table('slc_registrations')->whereIn('student_id', $ids)->where('academic_year_id', $yearId)->whereIn('slc_registration_status_id', [2, 4])
            ->whereNull('deleted_at')->orderBy('id')->get(['id', 'student_id', 'student_course_relation_id', 'registration_year', 'slc_registration_status_id']) as $g) {
            if ((int) $g->student_course_relation_id === (int) ($students[$g->student_id]['crel_id'] ?? 0)) {
                $out[(int) $g->student_id] = ['id' => (int) $g->id, 'year' => (int) $g->registration_year, 'status' => (int) $g->slc_registration_status_id === 2 ? 'No' : 'N/R'];
            }
        }

        return $out;
    }

    /**
     * Why Bulk Attendance & Installment won't be able to record this line on the SLC tab (as AttendanceActions::plan
     * would say), or null: the term, its session term, or — for a fee-paying code with a Tuition Fee Loan — the
     * registration's agreement the instalment goes on. A missing registration is step 4's own "noreg".
     */
    private static function recordBlock(array $s, ?array $l, ?string $code, float $tfl, $agreement): ?string
    {
        if (!$s['crel_id']) {
            return 'no active course';
        }
        if (!$s['instance']) {
            return 'no course instance for the year';
        }
        if (!$l || !$l['termDeclarationId']) {
            return null; // shown as "No term found" already
        }
        if (!$l['sessionTerm']) {
            return 'no session term (Term 1–3) for '.$l['decl'].' on the course instance';
        }
        $code = strtoupper((string) $code);
        if ($s['registration'] && $tfl > 0 && in_array($code, AttendanceCodes::FEE_PAYING, true) && !$agreement) {
            return 'no SLC agreement for Year '.$s['registration']['year'].' on the Accounts tab, needed for the '.$code.' instalment';
        }

        return null;
    }

    /**
     * The students and liability periods of any lines (a batch's lines may be from an earlier upload):
     * Bulk Attendance & Installment records the attendance against that term.
     *
     * @return array{0: array, 1: array} [snapshots by student id, liability period by student id]
     */
    public function periodsFor($rows, ?int $yearId): array
    {
        $students = StudentSnapshots::load($rows->pluck('student_id')->filter()->all(), $yearId);

        return [$students, $this->liability($students, $rows)];
    }

    /**
     * Step 4 (Attendance & codes): the liability period of each matched student and the register inside it, up to today.
     *
     * The period (user, 13 Nov 2026): SIS applies a code to the student's earliest outstanding liability period
     * (spec v9.0 §4.1.7), and field 8 of the line is only the course START period (AUT for a September
     * starter all year) — so the period is the first session term (1–3) of the year's SLC registration with no
     * attendance on the SLC tab confirmed before SIS issued this report (its schedule issue date: what SIS
     * knew), and its term is the course instance's term with that session term (instance_terms). One confirmed
     * on or after that day shows as already on the SLC tab for the period, and Bulk Attendance & Installment asks. Without one: the line's term (AUT…) on the
     * instance, else that term among the year's terms their class plans are in.
     */
    private function liability(array $students, $rows): array
    {
        $termOf = [];
        $uploadedAt = [];
        $issued = DB::table('slc_attendance_imports')->whereIn('id', $rows->pluck('slc_attendance_import_id')->filter()->unique()->all())->get(['id', 'report_date', 'created_at'])
            ->mapWithKeys(fn ($i) => [$i->id => substr((string) ($i->report_date ?: $i->created_at), 0, 10)]);
        foreach ($rows as $row) {
            if ($row->student_id) {
                $termOf[$row->student_id] = strtoupper((string) ($row->data['term'] ?? 'AUT'));
                $uploadedAt[$row->student_id] = (string) ($issued[$row->slc_attendance_import_id] ?? now()->toDateString());
            }
        }
        if (!$termOf) {
            return [];
        }
        // the periods already confirmed on the SLC tab, per registration (session term), when they were recorded
        $regIds = collect($termOf)->keys()->map(fn ($sid) => $students[$sid]['registration']['id'] ?? null)->filter()->unique()->all();
        $confirmed = $regIds ? DB::table('slc_attendances')->whereIn('slc_registration_id', $regIds)->whereNull('deleted_at')->whereNotNull('session_term')
            ->get(['slc_registration_id', 'session_term', 'confirmation_date', 'created_at'])->groupBy('slc_registration_id') : collect();
        $periodOf = [];
        foreach ($termOf as $sid => $code) {
            $regId = $students[$sid]['registration']['id'] ?? null;
            $done = collect($regId ? $confirmed->get($regId, []) : [])->filter(fn ($a) => substr((string) ($a->confirmation_date ?: $a->created_at), 0, 10) < $uploadedAt[$sid])
                ->pluck('session_term')->map(fn ($t) => (int) $t)->all();
            $periodOf[$sid] = collect([1, 2, 3])->first(fn ($n) => !in_array($n, $done, true)); // null: all three confirmed
        }
        $instIds = collect($students)->pluck('instance.id')->filter()->unique()->all();
        $iterms = $instIds ? DB::table('instance_terms as t')->join('term_declarations as d', 'd.id', '=', 't.term_declaration_id')->leftJoin('term_types as y', 'y.id', '=', 'd.term_type_id')
            ->whereIn('t.course_creation_instance_id', $instIds)->whereNull('t.deleted_at')
            ->get(['t.course_creation_instance_id', 't.start_date', 't.end_date', 't.term_declaration_id', 't.session_term', 'y.code', 'd.name as decl']) : collect();
        $periods = [];
        $missing = [];
        foreach ($termOf as $sid => $code) {
            $s = $students[$sid] ?? null;
            $onInstance = $s && $s['instance'] ? $iterms->filter(fn ($x) => (int) $x->course_creation_instance_id === (int) $s['instance']['id']) : collect();
            $t = ($periodOf[$sid] ? $onInstance->first(fn ($x) => (int) $x->session_term === $periodOf[$sid]) : null)
                ?? $onInstance->first(fn ($x) => strtoupper((string) $x->code) === $code);
            if ($t) {
                $termOf[$sid] = strtoupper((string) $t->code) ?: $code; // the period's own term (Autumn / Spring / Summer)
                $periods[$sid] = ['start' => substr((string) $t->start_date, 0, 10), 'end' => substr((string) $t->end_date, 0, 10), 'decl' => $t->decl, 'termDeclarationId' => (int) $t->term_declaration_id, 'sessionTerm' => (int) $t->session_term, 'from' => 'instance'];
            } else {
                $missing[] = $sid;
            }
        }
        if ($missing) {
            // only the report's academic year (user, 13 Nov 2026: a student with no plans yet this year was given last
            // year's term); the period's term is the n-th of the year's terms they are planned in (session term n)
            $ay = DB::table('slc_attendance_imports as i')->join('academic_years as y', 'y.id', '=', 'i.academic_year_id')
                ->whereIn('i.id', $rows->pluck('slc_attendance_import_id')->filter()->unique()->all())->first(['y.from_date', 'y.to_date']);
            $planTerms = DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')->join('term_declarations as d', 'd.id', '=', 'p.term_declaration_id')->leftJoin('term_types as y', 'y.id', '=', 'd.term_type_id')
                ->whereIn('a.student_id', $missing)->whereNull('a.deleted_at')->whereNull('p.deleted_at')
                ->when($ay && $ay->from_date && $ay->to_date, fn ($q) => $q->whereBetween('d.start_date', [substr((string) $ay->from_date, 0, 10), substr((string) $ay->to_date, 0, 10)]))
                ->select('a.student_id', 'd.id', 'd.name', 'd.start_date', 'd.end_date', 'y.code')->distinct()->orderBy('d.start_date')->get()->groupBy('student_id');
            foreach ($missing as $sid) {
                $yearTerms = collect($planTerms->get($sid, []))->unique('id')->sortBy('start_date')->values();
                $n = $periodOf[$sid] ?? null;
                $byOrder = $n && $yearTerms->count() >= $n ? $yearTerms[$n - 1] : null;
                $d = $byOrder ?? $yearTerms->filter(fn ($x) => strtoupper((string) $x->code) === $termOf[$sid])->last();
                if ($d) {
                    $termOf[$sid] = strtoupper((string) $d->code) ?: $termOf[$sid];
                    $periods[$sid] = ['start' => substr((string) $d->start_date, 0, 10), 'end' => substr((string) $d->end_date, 0, 10), 'decl' => $d->name, 'termDeclarationId' => (int) $d->id,
                        'sessionTerm' => $byOrder ? $n : null, 'from' => 'plans'];
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
                'term' => $code, 'termName' => $term, 'period' => $periodOf[$sid] ?? null, 'decl' => $p['decl'] ?? null, 'start' => $p['start'] ?? null, 'end' => $p['end'] ?? null, 'from' => $p['from'] ?? null,
                'termDeclarationId' => $p['termDeclarationId'] ?? null, 'sessionTerm' => $p['sessionTerm'] ?? null, 'started' => $p && $p['start'] <= $today,
                'st' => $st, 'text' => $text, 'pct' => $pct, 'last' => $last, 'first' => $a && $a->first ? substr((string) $a->first, 0, 10) : null,
                'a' => ['held' => $held, 'attended' => $attended, 'lastAgo' => $lastAgo, 'physical' => (int) ($a->physical ?? 0), 'online' => (int) ($a->online ?? 0),
                    'other' => max(0, $attended - (int) ($a->physical ?? 0) - (int) ($a->online ?? 0)), 'otherNames' => (string) ($a->other_names ?? '')],
            ];
        }

        return $out;
    }

    /** Step 6: every attendance batch with its students (any upload). */
    private function batches($batchById): array
    {
        if ($batchById->isEmpty()) {
            return [];
        }
        // the batch's lines, and those SIS rejected from it (user, 13 Nov 2026: back on step 4, maybe sent again in another batch)
        $items = SlcAttendanceImportRow::where(fn ($q) => $q->whereIn('slc_attendance_batch_id', $batchById->keys())
                ->orWhere(fn ($w) => $w->where('sis_rejected_item', true)->whereIn('sis_rejected_batch_id', $batchById->keys())))
            ->orderBy('confirmed_at')->orderBy('id')
            ->get(['id', 'slc_attendance_batch_id', 'ssn', 'student_id', 'confirmed_code', 'confirmed_by', 'confirmed_at', 'data', 'slc_attendance_id', 'slc_installment_id', 'slc_recorded', 'slc_recorded_by', 'slc_recorded_at',
                'sis_rejected_batch_id', 'sis_rejected_item', 'sis_rejected_code', 'sis_rejected_reason', 'sis_rejected_by', 'sis_rejected_at']);
        $amounts = DB::table('slc_installments')->whereIn('id', $items->pluck('slc_installment_id')->filter()->all())->pluck('amount', 'id');
        $students = DB::table('students')->whereIn('id', $items->pluck('student_id')->filter())->get(['id', 'first_name', 'last_name', 'registration_no'])->keyBy('id');
        $statuses = StudentStatus::forIds($items->pluck('student_id')->all());
        $names = RegistrationState::names(array_merge($items->pluck('confirmed_by')->all(), $items->pluck('slc_recorded_by')->all(), $items->pluck('sis_rejected_by')->all(), $batchById->flatMap(fn ($b) => [$b->created_by, $b->downloaded_by, $b->uploaded_by])->all()));
        $when = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('d/m H:i') : '';

        return $batchById->sortBy('id')->values()->map(fn ($b) => [
            'id' => $b->code, 'status' => $b->status, 'term' => $b->term, 'header' => $b->header,
            // the academic year to pick on SIS's Import Attendance screen: the file itself doesn't carry it
            'year' => $b->academic_year_id ? (string) DB::table('academic_years')->where('id', $b->academic_year_id)->value('name') : '',
            'items' => $items->filter(fn ($it) => (int) $it->slc_attendance_batch_id === (int) $b->id || ($it->sis_rejected_item && (int) $it->sis_rejected_batch_id === (int) $b->id))->values()->map(fn ($it) => [
                'rowId' => $it->id, 'ssn' => $it->ssn,
                'code' => (int) $it->slc_attendance_batch_id === (int) $b->id ? $it->confirmed_code : $it->sis_rejected_code,
                // rejected by SIS from this batch: when, why, and the batch it went in again (if confirmed since)
                'rejected' => $it->sis_rejected_item && (int) $it->sis_rejected_batch_id === (int) $b->id ? ['reason' => (string) $it->sis_rejected_reason,
                    'by' => $names[$it->sis_rejected_by] ?? '', 'at' => $when($it->sis_rejected_at),
                    'resent' => $it->slc_attendance_batch_id && (int) $it->slc_attendance_batch_id !== (int) $b->id ? ($batchById->get($it->slc_attendance_batch_id)?->code) : null] : null,
                'name' => trim(($students[$it->student_id]->first_name ?? '').' '.($students[$it->student_id]->last_name ?? '')), 'sid' => $students[$it->student_id]->registration_no ?? '',
                'url' => $it->student_id ? route('student.show', $it->student_id) : null, 'slcUrl' => $it->student_id ? route('student.slc.history', $it->student_id) : null, 'accountsUrl' => $it->student_id ? route('student.accounts', $it->student_id) : null,
                'by' => $names[$it->confirmed_by] ?? '', 'at' => $when($it->confirmed_at), 'status' => $statuses[$it->student_id] ?? null,
                // Bulk Attendance & Installment: the SLC attendance recorded on the student's SLC tab
                'record' => ['status' => $it->slc_recorded, 'by' => $it->slc_recorded_by ? ($names[$it->slc_recorded_by] ?? '') : '', 'at' => $when($it->slc_recorded_at),
                    'amount' => $it->slc_installment_id ? (float) ($amounts[$it->slc_installment_id] ?? 0) : null],
            ])->all(),
            'file' => $b->path ? ['name' => $b->file_name, 'date' => $b->file_date ? substr((string) $b->file_date, 0, 10) : null] : null,
            'downloadedBy' => $b->downloaded_by ? ($names[$b->downloaded_by] ?? '') : null, 'downloadedAt' => $when($b->downloaded_at),
            'uploadedBy' => $b->uploaded_by ? ($names[$b->uploaded_by] ?? '') : null, 'uploadedAt' => $when($b->uploaded_at),
        ])->all();
    }

    /** The SIS attendance codes, what they mean and whether they need a CoC (attendance_codes.coc_required). */
    private static function codes(): array
    {
        return collect(AttendanceCodes::table())->map(fn ($c, $code) => ['code' => $code, 'meaning' => AttendanceCodes::MEANING[$code] ?? '', 'coc' => $c['coc'], 'cocType' => $c['coc'] ? (AttendanceCodes::COC_TYPE[$code] ?? 'Outstanding') : null,
            'importable' => AttendanceCodes::importable($code)])->sortKeys()->values()->all(); // false: can't go in SLC's import file (D)
    }

    private static function studentOut(array $s): array
    {
        return [
            'id' => $s['reg_no'], 'sid' => $s['id'], 'title' => $s['title'], 'first' => $s['first'], 'last' => $s['last'], 'dob' => $s['dob'], 'ssn' => $s['ssn'],
            'course' => $s['course_name'], 'intake' => $s['intake'], 'slcCode' => $s['slc_code'], 'year' => $s['instance']['rank'] ?? null,
            'registration' => $s['registration'], 'url' => route('student.show', $s['id']), 'status' => $s['status'] ?? null,
            'address' => $s['addresses'][0]['text'] ?? '', 'postcode' => $s['addresses'][0]['postcode'] ?? '',
            'emails' => $s['emails'] ?? [], // the steps' search box (user, 13 Nov 2026)
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
