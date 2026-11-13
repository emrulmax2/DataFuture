<?php

namespace App\Services\SlcRegistration;

use App\Models\AcademicYear;
use App\Models\SlcAttendanceBatch;
use App\Models\SlcAttendanceImport;
use App\Models\SlcAttendanceImportRow;
use App\Models\SlcBulkActivity;
use App\Models\Student;
use App\Models\StudentArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Bulk attendance (user, 2 Nov 2026): upload the SIS Attendance Confirmation Report, match each
 * student by SSN (Save SSN for one not on our records), sort the Data check differences, choose each
 * student's attendance code, confirm it into the batch awaiting download — queuing a CoC for a code
 * that needs one — and download the batch as the SIS attendance import file. Every action is written
 * to the shared Activity log.
 */
class AttendanceActions
{
    private function user(): int
    {
        return (int) auth()->id();
    }

    private function log(string $text): void
    {
        SlcBulkActivity::create(['slc_bulk_import_id' => null, 'user_id' => $this->user(), 'text' => 'Bulk attendance – '.$text]);
    }

    private function fail(string $message): never
    {
        throw new SlcRegistrationException($message);
    }

    public function import(string $text, string $fileName): string
    {
        $p = AttendanceFile::parse($text);
        if (!$p['header']) {
            $this->fail('That file has no header line (HEI ~ name ~ date ~ count). Export the Attendance Confirmation Report from SIS and try again.');
        }
        if (!$p['rows']) {
            $this->fail('No student lines were found in that file.');
        }
        $date = Text::slcToIso($p['header']['date']);
        // the header carries no academic year: the one the report date falls in
        $year = $date ? AcademicYear::where('from_date', '<=', $date)->where('to_date', '>=', $date)->orderByDesc('id')->first() : null;
        if (!$year) {
            $this->fail('No academic year under Academic Years covers the report date '.Text::fmtIso($date).'.');
        }

        $disk = FileDisk::name();
        $path = 'slc-registration/attendance/'.now()->format('Ymd_His').'_'.Str::slug(pathinfo($fileName, PATHINFO_FILENAME)).'.txt';
        Storage::disk($disk)->put($path, $text);

        $match = self::matchBySsn($p['rows'], $year->id);
        $terms = array_values(array_unique(array_filter(array_column($p['rows'], 'term'))));
        $import = DB::transaction(function () use ($p, $fileName, $disk, $path, $date, $year, $match, $terms) {
            $import = SlcAttendanceImport::create([
                'file_name' => $fileName, 'disk' => $disk, 'path' => $path, 'header' => $p['header']['raw'], 'hei' => $p['header']['hei'], 'hei_name' => $p['header']['heiName'],
                'report_date' => $date, 'academic_year_id' => $year->id, 'term' => implode(',', $terms), 'row_count' => count($p['rows']),
                'totals' => $p['header']['totals'], 'error_lines' => $p['errors'], 'created_by' => $this->user(),
            ]);
            foreach ($p['rows'] as $r) {
                $raw = $r['raw'];
                $line = $r['line'];
                unset($r['raw'], $r['line']);
                $sid = $match[$r['ssn']] ?? null;
                SlcAttendanceImportRow::create([
                    'slc_attendance_import_id' => $import->id, 'line_no' => $line, 'raw' => $raw, 'ssn' => $r['ssn'], 'data' => $r,
                    'student_id' => $sid, 'match_method' => $sid ? 'ssn' : null,
                ]);
            }

            return $import;
        });
        $carried = $this->carryOver($import);

        $n = count($p['rows']);
        $matched = count(array_filter($match));
        $warn = $p['header']['count'] && $p['header']['count'] !== $n ? ' The header says '.$p['header']['count'].' students; the file has '.$n.'.' : '';
        $kept = $carried ? ' Work on '.Text::plural($carried, 'student').' carried over from the earlier upload.' : '';
        $this->log('uploaded '.$fileName.' ('.AttendanceFile::termName($terms[0] ?? '').' '.$year->name.') – '.$n.' students, '.$matched.' matched by SSN, '.($n - $matched).' to match.'.$warn.$kept);

        return 'Uploaded '.$n.' students. '.$matched.' matched by SSN, '.($n - $matched).' to match.'.$warn.$kept;
    }

    /**
     * SSN → student id. An SSN on more than one student record (returning students) goes to the one
     * whose course carries the line's SLC code, else one with an active course, else the newest.
     */
    public static function matchBySsn(array $rows, int $yearId): array
    {
        $ssns = array_values(array_unique(array_filter(array_column($rows, 'ssn'))));
        $holders = DB::table('students')->whereIn('ssn_no', $ssns)->whereNull('deleted_at')->orderByDesc('id')->get(['id', 'ssn_no'])->groupBy('ssn_no');
        $multi = $holders->filter(fn ($g) => $g->count() > 1)->flatten(1)->pluck('id')->all();
        $snap = $multi ? StudentSnapshots::load($multi, $yearId) : [];
        $out = [];
        foreach ($rows as $r) {
            $group = $holders->get($r['ssn']);
            if (!$group) {
                $out[$r['ssn']] = null;
                continue;
            }
            $pick = $group->first();
            if ($group->count() > 1) {
                $pick = $group->first(fn ($g) => ($snap[$g->id]['slc_code'] ?? '') === strtoupper($r['slcCode'] ?? ''))
                    ?? $group->first(fn ($g) => !empty($snap[$g->id]['crel_id'])) ?? $group->first();
            }
            $out[$r['ssn']] = (int) $pick->id;
        }

        return $out;
    }

    /* ---------------------------------------------------------- data check */

    /** The line's student and checks, as the Data check shows them. */
    private function rowInfo(SlcAttendanceImportRow $row): array
    {
        $yearId = (int) $row->import->academic_year_id;
        $s = $row->student_id ? (StudentSnapshots::load([$row->student_id], $yearId)[$row->student_id] ?? null) : null;
        if (!$s) {
            $this->fail('This line is not matched to a student.');
        }
        $agr = $s['registration'] ? DB::table('slc_agreements')->where('slc_registration_id', $s['registration']['id'])->whereNull('deleted_at')->latest('id')->first(['fees', 'commission_amount', 'discount', 'total', 'slc_coursecode']) : null;
        $checks = AttendanceChecks::run($row->data, $s, $agr ? (array) $agr : null, (string) (\App\Models\AcademicYear::find($yearId)->name ?? ''));

        return [$s, collect($checks)->keyBy('key')];
    }

    public function setRoute(int $rowId, string $key, string $route, bool $quiet = false): string
    {
        $row = SlcAttendanceImportRow::with(['fields', 'import'])->find($rowId) ?? $this->fail('That line is no longer in the upload.');
        [$s, $checks] = $this->rowInfo($row);
        $ch = $checks->get($key);
        if (!$ch || $ch['ok']) {
            $this->fail('That field has nothing to sort.');
        }
        if (!in_array($route, AttendanceChecks::FIELD_ROUTES[$key] ?? [], true)) {
            $this->fail('That action is not available for '.strtolower(AttendanceChecks::FIELD_LABEL[$key]).'.');
        }
        $name = $s['first'].' '.$s['last'];
        $label = strtolower(AttendanceChecks::FIELD_LABEL[$key]);

        if ($route === 'update') {
            $st = Student::findOrFail($s['id']);
            $r = $row->data;
            [$field, $value] = match ($key) {
                'first' => ['first_name', Text::titleCase($r['forename'])],
                'last' => ['last_name', Text::titleCase($r['surname'])],
                'dob' => ['date_of_birth', Text::slcToIso($r['dob'])],
            };
            DB::transaction(function () use ($st, $field, $value, $ch, $row) {
                $old = $st->getRawOriginal($field);
                $st->{$field} = $value;
                $st->updated_by = $this->user();
                $st->save();
                StudentArchive::create(['student_id' => $st->id, 'table' => 'students', 'field_name' => $field, 'field_value' => $old, 'field_new_value' => $value, 'created_by' => $this->user()]);
                \App\Models\SlcBulkUpdate::create(['slc_bulk_import_row_id' => null, 'student_id' => $st->id, 'field' => $ch['label'], 'from_value' => $ch['ours'], 'to_value' => $ch['slc'], 'created_by' => $this->user()]);
            });
            if (!$quiet) {
                $this->log('updated our record '.$s['reg_no'].' '.$name.': '.$label.' “'.$ch['ours'].'” → “'.$ch['slc'].'”');
            }

            return 'Updated '.$name.'’s '.$label.' to '.$ch['slc'].'.';
        }

        DB::transaction(function () use ($row, $key, $route, $s, $ch) {
            $f = \App\Models\SlcAttendanceRowField::firstOrNew(['slc_attendance_import_row_id' => $row->id, 'field' => $key]);
            $this->dropCoc($f);
            $f->fill(['route' => $route, 'created_by' => $this->user()]);
            if ($route === 'coc') {
                $f->slc_coc_id = $this->coc($s, $ch)->id;
            }
            $f->save();
        });
        if (!$quiet) {
            $this->log(($route === 'coc' ? 'CoC to issue ('.AttendanceChecks::COC_TYPE[$key].')' : ($route === 'contact' ? 'student to contact about '.$label : 'no action needed on '.$label)).' – '.$name);
        }

        return $route === 'coc' ? 'CoC to issue added for '.$name.'.' : ($route === 'contact' ? $name.' added to students to contact.' : 'No action needed on '.$label.' for '.$name.'.');
    }

    /**
     * The Data check's Name row (user, 6 Nov 2026: first and last name as one "Name"): one action for
     * several differences of a line, all or none. Fields already matching or decided are skipped.
     */
    public function setRoutes(int $rowId, array $keys, string $route): string
    {
        $keys = array_values(array_intersect(array_keys(AttendanceChecks::FIELD_ROUTES), array_unique($keys)));
        if (!$keys) {
            $this->fail('Nothing to decide.');
        }
        foreach ($keys as $k) {
            if (!in_array($route, AttendanceChecks::FIELD_ROUTES[$k], true)) {
                $this->fail('That action is not available for '.strtolower(AttendanceChecks::FIELD_LABEL[$k]).'. Decide each field separately.');
            }
        }
        $done = [];
        DB::transaction(function () use ($rowId, $keys, $route, &$done) {
            foreach ($keys as $k) {
                $row = SlcAttendanceImportRow::with(['fields', 'import'])->find($rowId) ?? $this->fail('That line is no longer in the upload.');
                [, $checks] = $this->rowInfo($row);
                $ch = $checks->get($k);
                if (!$ch || $ch['ok'] || $row->fields->firstWhere('field', $k)) {
                    continue;
                }
                $this->setRoute($rowId, $k, $route, true);
                $done[] = strtolower(AttendanceChecks::FIELD_LABEL[$k]);
            }
        });
        if (!$done) {
            $this->fail('Those fields have nothing left to decide.');
        }
        $who = $this->who(SlcAttendanceImportRow::find($rowId));
        $what = count($done) > 1 ? implode(', ', array_slice($done, 0, -1)).' and '.end($done) : $done[0];
        $this->log(match ($route) {
            'update' => 'updated our record – '.$who.': '.$what,
            'coc' => 'CoC to issue – '.$who.': '.$what,
            'contact' => 'student to contact about '.$what.' – '.$who,
            default => 'no action needed on '.$what.' – '.$who,
        });

        return match ($route) {
            'update' => 'Updated '.$who.'’s '.$what.' from SLC.',
            'coc' => 'CoC to issue added for '.$who.' ('.$what.').',
            'contact' => $who.' added to students to contact ('.$what.').',
            default => 'No action needed on '.$what.' for '.$who.'.',
        };
    }

    /** The Name row's Change: each of those fields is open to decide again. */
    public function clearRoutes(int $rowId, array $keys): string
    {
        $keys = array_values(array_intersect(array_keys(AttendanceChecks::FIELD_ROUTES), array_unique($keys)));
        DB::transaction(function () use ($rowId, $keys) {
            foreach ($keys as $k) {
                $this->clearRoute($rowId, $k);
            }
        });

        return 'Reopened '.implode(' and ', array_map(fn ($k) => strtolower(AttendanceChecks::FIELD_LABEL[$k]), $keys)).'.';
    }

    public function clearRoute(int $rowId, string $key): string
    {
        $row = SlcAttendanceImportRow::with('fields')->find($rowId) ?? $this->fail('That line is no longer in the upload.');
        $f = $row->fields->firstWhere('field', $key);
        if ($f) {
            DB::transaction(function () use ($f) {
                $this->dropCoc($f);
                $f->delete();
            });
        }
        $st = Student::find($row->student_id);
        $this->log('reopened '.strtolower(AttendanceChecks::FIELD_LABEL[$key] ?? $key).' for '.trim(($st->first_name ?? '').' '.($st->last_name ?? '')));

        return 'Reopened '.strtolower(AttendanceChecks::FIELD_LABEL[$key] ?? $key).'.';
    }

    /** Every suggested decision on those lines; with $key, only that field's (Data check grouped by issue). */
    public function applySuggested(array $rowIds, ?string $key = null): string
    {
        $n = 0;
        $students = 0;
        foreach (SlcAttendanceImportRow::with(['fields', 'import'])->whereIn('id', $rowIds)->whereNotNull('student_id')->get() as $row) {
            [, $checks] = $this->rowInfo($row);
            $routed = $row->fields->pluck('field')->all();
            $done = 0;
            foreach ($checks as $ch) {
                $sugg = $ch['ok'] ? null : AttendanceChecks::suggest($ch);
                if ($sugg && !in_array($ch['key'], $routed, true) && ($key === null || $ch['key'] === $key)) {
                    $this->setRoute($row->id, $ch['key'], $sugg, true);
                    $done++;
                }
            }
            $n += $done;
            $students += $done ? 1 : 0;
        }
        if ($n) {
            $this->log('applied suggested actions to '.Text::plural($n, 'difference').' across '.Text::plural($students, 'student'));
        }

        return $n ? 'Applied '.Text::plural($n, 'suggested action').($key ? '.' : '. Date of birth, SSN and missing registrations still need a decision.') : 'Nothing to apply.';
    }

    /** The CoC on the student's SLC tab, linked to the year's registration; an open one of the same type raised here is used again. */
    private function coc(array $s, array $ch): \App\Models\SlcCoc
    {
        if (!$s['crel_id']) {
            $this->fail('The student has no active course to raise a CoC against.');
        }
        $reason = 'SLC attendance check – '.AttendanceChecks::COC_TYPE[$ch['key']].': '.$ch['label'].' SLC '.$ch['slc'].', ours '.$ch['ours'];
        $open = $this->openCoc($s['id'], AttendanceChecks::COC_ENUM[$ch['key']]);
        if ($open) {
            $open->update(['reason' => $reason, 'updated_by' => $this->user()]);

            return $open;
        }

        return \App\Models\SlcCoc::create([
            'student_id' => $s['id'], 'student_course_relation_id' => $s['crel_id'], 'course_creation_instance_id' => $s['instance']['id'] ?? null,
            'slc_registration_id' => $s['registration']['id'] ?? null, 'confirmation_date' => now()->toDateString(),
            'coc_type' => AttendanceChecks::COC_ENUM[$ch['key']], 'reason' => $reason, 'actioned' => 'no', 'created_by' => $this->user(),
        ]);
    }

    private function dropCoc(\App\Models\SlcAttendanceRowField $f): void
    {
        if ($f->slc_coc_id) {
            $shared = \App\Models\SlcAttendanceRowField::where('slc_coc_id', $f->slc_coc_id)->where('id', '!=', $f->id ?? 0)->exists()
                || \App\Models\SlcBulkRowField::where('slc_coc_id', $f->slc_coc_id)->exists()
                || SlcAttendanceImportRow::where('slc_coc_id', $f->slc_coc_id)->exists();
            if (!$shared) {
                \App\Models\SlcCoc::where('id', $f->slc_coc_id)->where('actioned', 'no')->delete();
            }
            $f->slc_coc_id = null;
        }
    }

    /** An open CoC of that type raised by bulk registration or bulk attendance, to use again rather than raise a second. */
    private function openCoc(int $studentId, string $type): ?\App\Models\SlcCoc
    {
        return \App\Models\SlcCoc::where('student_id', $studentId)->where('coc_type', $type)->where('actioned', 'no')
            ->where(fn ($q) => $q->where('reason', 'like', 'SLC attendance%')->orWhere('reason', 'like', 'SLC registration check%'))->latest('id')->first();
    }

    /* ---------------------------------------------------- step 4: the code */

    /** The code staff choose for a line (null = back to the suggestion). Any code can be chosen. */
    public function setCode(int $rowId, ?string $code): string
    {
        $row = SlcAttendanceImportRow::find($rowId) ?? $this->fail('That line is no longer in the upload.');
        if (!$row->student_id) {
            $this->fail('Match the student first.');
        }
        if ($row->confirmed_code) {
            $this->fail('This code is already confirmed. Remove it from the batch to change it.');
        }
        $code = $code !== null && $code !== '' ? strtoupper($code) : null;
        if ($code !== null && !isset(AttendanceCodes::table()[$code])) {
            $this->fail('Unknown attendance code '.$code.'.');
        }
        if ($code !== null && !AttendanceCodes::importable($code)) {
            $this->fail('SLC’s attendance import only accepts A, C, D, F, L, N, S or X, so '.$code.' can’t go in the file. Record it for this student directly in SIS.');
        }
        if ($code === 'D' && ($why = $this->neverAttendedRefusal($row))) {
            $this->fail($why);
        }
        // spec v9.0 §7.2.3: no "in attendance" code (nor D) for a student who is Not Registered — X or S only
        if ($code !== null && !in_array($code, AttendanceCodes::NOT_REGISTERED, true) && $this->registrationStatus($row) === 'not') {
            $this->fail('This student is recorded as Not Registered with SLC for the year, so SIS takes only X (not in attendance) or S (suspended).');
        }
        $row->update(['attendance_code' => $code, 'code_by' => $code ? $this->user() : null, 'code_at' => $code ? now() : null]);
        $who = $this->who($row);
        $this->log($code ? 'chose code '.$code.' ('.(AttendanceCodes::MEANING[$code] ?? '').') for '.$who : 'put '.$who.' back to the suggested code');

        return $code ? 'Code '.$code.' chosen for '.$who.'.' : $who.' is back on the suggested code.';
    }

    /**
     * Step 4's Session Term and Attendance Term (user, 13 Nov 2026): what Bulk Attendance & Installment records the
     * attendance (and instalment) with on the SLC tab — null for either goes back to the suggested one (RecordTerm).
     * Only until the line is recorded there; a line whose confirmation is in an earlier upload's batch is set too.
     */
    public function setRecordTerm(int $rowId, ?int $session, ?int $termDeclarationId): string
    {
        $row = SlcAttendanceImportRow::find($rowId) ?? $this->fail('That line is no longer in the upload.');
        if (!$row->student_id) {
            $this->fail('Match the student first.');
        }
        if ($session !== null && !in_array($session, RecordTerm::SESSIONS, true)) {
            $this->fail('Session Term is 1 to 4.');
        }
        if ($termDeclarationId !== null && !DB::table('term_declarations')->where('id', $termDeclarationId)->whereNull('deleted_at')->exists()) {
            $this->fail('That attendance term no longer exists.');
        }
        $lines = collect([$row]);
        if ($row->earlier_batch_id) {
            $lines = $lines->merge(SlcAttendanceImportRow::where('slc_attendance_batch_id', $row->earlier_batch_id)->where('ssn', $row->ssn)->get());
        }
        if ($lines->contains(fn ($l) => $l->slc_recorded)) {
            $this->fail('This attendance is already recorded on the SLC tab – change it there.');
        }
        $chosen = $session !== null || $termDeclarationId !== null;
        $lines->each(fn ($l) => $l->update(['record_session_term' => $session, 'record_term_declaration_id' => $termDeclarationId,
            'record_term_by' => $chosen ? $this->user() : null, 'record_term_at' => $chosen ? now() : null]));
        $who = $this->who($row);
        $what = trim(($session ? 'Term '.$session : '').($termDeclarationId ? ' · '.DB::table('term_declarations')->where('id', $termDeclarationId)->value('name') : ''), ' ·');
        $this->log($chosen ? 'chose '.$what.' for the SLC tab attendance of '.$who : 'put '.$who.' back to the suggested SLC tab term');

        return $chosen ? $what.' chosen for '.$who.'.' : $who.' is back on the suggested term.';
    }

    /**
     * Confirm the code of each line that is ready (matched, an SLC registration recorded for the year —
     * user, 2 Nov 2026: without one the code must not be submitted — the Data check sorted, a code
     * chosen or suggested): it goes into the batch awaiting download for the academic year, HEI and
     * term, and a code that needs a CoC queues one on the student's SLC tab.
     */
    public function confirm(array $rowIds, array $includeOnSlcTab = []): string
    {
        $state = app(AttendanceState::class)->build();
        $asked = collect($state['rows'])->whereIn('id', $rowIds);
        // the period already on the SLC tab (entered by hand after SIS issued the report — probably confirmed in SIS
        // too, and SIS refuses a second confirmation): only those ticked to send anyway go in (user, 13 Nov 2026)
        $leftOut = $asked->where('codeStage', 'ready')->filter(fn ($i) => self::onSlcTab($i) && !in_array($i['id'], $includeOnSlcTab, true));
        $asked = $asked->reject(fn ($i) => $leftOut->contains('id', $i['id']));
        if ($asked->isEmpty() && $leftOut->isNotEmpty()) {
            $this->fail('Nothing confirmed: '.($leftOut->count() === 1 ? 'that student’s' : 'those students’').' attendance for the term is already on the SLC tab or in SIS.');
        }
        // only codes SLC's import accepts (spec v9.0 Appendix B) go into a batch, and D only where SLC takes it
        $ready = $asked->where('codeStage', 'ready');
        $notImportable = $ready->reject(fn ($i) => AttendanceCodes::importable($i['code']['final']))->count();
        $dRefused = [];
        foreach ($ready->where('code.final', 'D') as $i) {
            if ($why = $this->neverAttendedRefusal(SlcAttendanceImportRow::with('import')->find($i['id']))) {
                $dRefused[$i['id']] = $why;
            }
        }
        $items = $ready->filter(fn ($i) => AttendanceCodes::importable($i['code']['final']) && !isset($dRefused[$i['id']]))->values();
        if ($items->isEmpty() && $dRefused) {
            $this->fail(count($dRefused) === 1 ? reset($dRefused) : Text::plural(count($dRefused), 'student').' can’t be sent with D: SLC only takes it for a student with no registration for the year and no attendance confirmed.');
        }
        if ($items->isEmpty() && $notImportable) {
            $this->fail('SLC’s attendance import only accepts A, C, D, F, L, N, S or X. Choose one of those, or record the code directly in SIS.');
        }
        if ($items->isEmpty()) {
            $noReg = $asked->where('codeStage', 'noreg')->count();
            $this->fail($noReg ? ($noReg === 1 ? 'This student has' : $noReg.' students have').' no SLC registration recorded for the year. Record it first (SLC tab or Bulk registration).' : 'None of those students can be confirmed yet.');
        }
        $rows = SlcAttendanceImportRow::with('import')->whereIn('id', $items->pluck('id'))->get()->keyBy('id');
        $cocs = 0;
        $batches = DB::transaction(function () use ($items, $rows, &$cocs) {
            $used = [];
            foreach ($items as $i) {
                $row = $rows[$i['id']];
                $code = $i['code']['final'];
                $batch = $this->awaitingBatch($row);
                $upd = ['confirmed_code' => $code, 'confirmed_by' => $this->user(), 'confirmed_at' => now(), 'slc_attendance_batch_id' => $batch->id, 'earlier_batch_id' => null];
                if (AttendanceCodes::needsCoc($code)) {
                    $upd['slc_coc_id'] = $this->codeCoc($i, $code)->id;
                    $cocs++;
                }
                $row->update($upd);
                $used[$batch->code] = true;
            }

            return array_keys($used);
        });
        $first = $items->first()['s'];
        $who = $items->count() === 1 ? $first['first'].' '.$first['last'].' ('.$items->first()['code']['final'].')' : Text::plural($items->count(), 'student');
        $coc = $cocs ? ' – '.Text::plural($cocs, 'CoC').' queued' : '';
        $this->log('confirmed codes for '.$who.' – added to batch '.implode(', ', $batches).$coc);
        $skipped = count(array_unique($rowIds)) - $items->count() - $leftOut->count();
        if ($leftOut->isNotEmpty()) {
            $this->log('left out of the batch, their attendance for the term already on the SLC tab or in SIS: '.$leftOut->map(fn ($i) => $i['s']['id'])->implode(', '));
        }

        return 'Confirmed '.$who.' into batch '.implode(', ', $batches).'.'.($cocs ? ' '.Text::plural($cocs, 'CoC').' queued.' : '')
            .($leftOut->isNotEmpty() ? ' '.Text::plural($leftOut->count(), 'student').' left out – already on the SLC tab or in SIS.' : '')
            .($skipped > 0 ? ' '.Text::plural($skipped, 'student').' not ready yet were left out.' : '');
    }

    /**
     * A step 4 row that may already be confirmed: its liability period on the SLC tab, entered by hand (not recorded
     * by this module), or a code SIS already has saved or submitted for it (the report's column 28).
     */
    private static function onSlcTab(array $i): bool
    {
        return (!empty($i['slcTab']['attendance']) && empty($i['slcTab']['recorded'])) || !empty($i['sisCode']);
    }

    /** Ticked students ready to confirm whose period is already on the SLC tab: the page asks about them first. */
    public function alreadyOnSlcTab(array $rowIds): array
    {
        return collect(app(AttendanceState::class)->build()['rows'])->whereIn('id', $rowIds)->where('codeStage', 'ready')
            ->filter(fn ($i) => self::onSlcTab($i))->map(fn ($i) => [
                'rowId' => $i['id'], 'name' => trim($i['s']['first'].' '.$i['s']['last']), 'sid' => $i['s']['id'], 'status' => $i['s']['status'] ?? null,
                'code' => $i['code']['final'], 'term' => $i['liab']['decl'] ?? null, 'sessionTerm' => $i['liab']['sessionTerm'] ?? null,
                'onTab' => !empty($i['slcTab']['recorded']) ? null : $i['slcTab'], 'sisCode' => $i['sisCode'] ?? null,
                'slcUrl' => $i['s']['slcUrl'] ?? null, 'accountsUrl' => $i['s']['accountsUrl'] ?? null,
            ])->values()->all();
    }

    /** One waiting batch per academic year + HEI + term; it sends the newest upload's header. */
    private function awaitingBatch(SlcAttendanceImportRow $row): SlcAttendanceBatch
    {
        $imp = $row->import;
        $term = strtoupper((string) ($row->data['term'] ?? ''));
        $batch = SlcAttendanceBatch::where('status', 'awaiting')->where('academic_year_id', $imp->academic_year_id)->where('term', $term)->lockForUpdate()->get()
            ->first(fn ($b) => explode('~', $b->header)[0] === $imp->hei);
        if (!$batch) {
            $next = (int) SlcAttendanceBatch::pluck('code')->map(fn ($c) => (int) preg_replace('/\D/', '', $c))->max() + 1;

            return SlcAttendanceBatch::create(['code' => 'ATT-B'.str_pad((string) $next, 3, '0', STR_PAD_LEFT), 'status' => 'awaiting', 'header' => $imp->header, 'term' => $term, 'academic_year_id' => $imp->academic_year_id, 'created_by' => $this->user()]);
        }
        if ($batch->header !== $imp->header && $imp->id >= (int) SlcAttendanceImport::where('header', $batch->header)->max('id')) {
            $batch->update(['header' => $imp->header]);
        }

        return $batch;
    }

    /** The CoC a code needs (attendance_codes.coc_required), on the student's SLC tab, linked to the year's registration. */
    private function codeCoc(array $i, string $code): \App\Models\SlcCoc
    {
        $s = $i['s'];
        $snap = StudentSnapshots::load([$s['sid']], (int) SlcAttendanceImport::where('id', SlcAttendanceImportRow::where('id', $i['id'])->value('slc_attendance_import_id'))->value('academic_year_id'))[$s['sid']] ?? null;
        if (!$snap || !$snap['crel_id']) {
            $this->fail($s['first'].' '.$s['last'].' has no active course to raise a CoC against.');
        }
        $type = AttendanceCodes::COC_TYPE[$code] ?? 'Outstanding';
        $l = $i['liab'];
        $reason = 'SLC attendance code '.$code.' – '.(AttendanceCodes::MEANING[$code] ?? '').'. '.($l && $l['start'] ? $l['termName'].' term '.Text::fmtIso($l['start']).' – '.Text::fmtIso($l['end']).': '.$l['a']['attended'].' of '.$l['a']['held'].' sessions attended, last attended '.($l['last'] ? Text::fmtIso($l['last']) : 'never').'.' : '');
        $open = $this->openCoc($snap['id'], $type);
        if ($open) {
            $open->update(['reason' => trim($open->reason."\n".$reason), 'updated_by' => $this->user()]);

            return $open;
        }

        return \App\Models\SlcCoc::create([
            'student_id' => $snap['id'], 'student_course_relation_id' => $snap['crel_id'], 'course_creation_instance_id' => $snap['instance']['id'] ?? null,
            'slc_registration_id' => $snap['registration']['id'] ?? null, 'confirmation_date' => now()->toDateString(),
            'coc_type' => $type, 'reason' => $reason, 'actioned' => 'no', 'created_by' => $this->user(),
        ]);
    }

    /** Take a line out of the batch awaiting download (its queued CoC goes too, unless something else uses it). */
    public function undoConfirm(int $rowId): string
    {
        $row = SlcAttendanceImportRow::find($rowId) ?? $this->fail('That line is no longer in the upload.');
        if (!$row->confirmed_code) {
            return 'Nothing to remove.';
        }
        $batch = SlcAttendanceBatch::find($row->slc_attendance_batch_id ?: $row->earlier_batch_id);
        if ($batch && $batch->status !== 'awaiting') {
            $this->fail('That batch has already been downloaded.');
        }
        DB::transaction(function () use ($row, $batch) {
            $cocId = $row->slc_coc_id;
            $row->update(['confirmed_code' => null, 'confirmed_by' => null, 'confirmed_at' => null, 'slc_attendance_batch_id' => null, 'earlier_batch_id' => null, 'slc_coc_id' => null]);
            if ($cocId && !SlcAttendanceImportRow::where('slc_coc_id', $cocId)->exists() && !\App\Models\SlcAttendanceRowField::where('slc_coc_id', $cocId)->exists() && !\App\Models\SlcBulkRowField::where('slc_coc_id', $cocId)->exists()) {
                \App\Models\SlcCoc::where('id', $cocId)->where('actioned', 'no')->where('reason', 'like', 'SLC attendance code%')->delete();
            }
            if ($batch && !SlcAttendanceImportRow::where('slc_attendance_batch_id', $batch->id)->exists()) {
                $batch->delete();
            }
        });
        $this->log('removed '.$this->who($row).' from batch '.($batch->code ?? ''));

        return 'Removed '.$this->who($row).' from batch '.($batch->code ?? '').'.';
    }

    /* ------------------------------------------------------- step 5: CoCs */

    /** A queued CoC is done in SIS (or back to queued): slc_cocs.actioned, as on the student's SLC tab. */
    public function cocDone(int $rowId, bool $done): string
    {
        $row = SlcAttendanceImportRow::find($rowId) ?? $this->fail('That line is no longer in the upload.');
        $coc = $row->slc_coc_id ? \App\Models\SlcCoc::find($row->slc_coc_id) : null;
        if (!$coc) {
            $this->fail('No CoC is queued for this student.');
        }
        $coc->update(['actioned' => $done ? 'Yes' : 'no', 'updated_by' => $this->user()]);
        $this->log(($done ? 'marked the '.$coc->coc_type.' CoC done in SIS' : 'reopened the '.$coc->coc_type.' CoC').' – '.$this->who($row));

        return $done ? $coc->coc_type.' CoC marked done for '.$this->who($row).'.' : $coc->coc_type.' CoC reopened for '.$this->who($row).'.';
    }

    /* ---------------------------------------------------- step 6: batches */

    /** Generates the SIS attendance import file the first time (batch → downloaded); later calls return the same file. */
    public function download(string $code): array
    {
        $batch = SlcAttendanceBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status === 'awaiting') {
            $items = SlcAttendanceImportRow::where('slc_attendance_batch_id', $batch->id)->orderBy('confirmed_at')->orderBy('id')->get();
            if ($items->isEmpty()) {
                $this->fail('The batch is empty.');
            }
            $text = $this->buildBatchText($batch, now());
            $name = AttendanceFile::fileName(explode('~', $batch->header)[0], (string) $batch->term, $batch->code);
            $disk = FileDisk::name();
            $path = 'slc-registration/attendance-batches/'.$name;
            try {
                $saved = (bool) Storage::disk($disk)->put($path, $text);
            } catch (\Throwable $e) {
                $saved = false;
            }
            if (!$saved) { // the disks don't throw: say so in the log, it is built again when needed
                \Illuminate\Support\Facades\Log::warning('SLC attendance: the batch file '.$path.' could not be saved to the '.$disk.' disk.');
            }
            $batch->update(['status' => 'downloaded', 'file_name' => $name, 'disk' => $disk, 'path' => $path, 'file_date' => now()->toDateString(), 'downloaded_by' => $this->user(), 'downloaded_at' => now()]);
            $this->log('generated SIS file '.$name.' for batch '.$batch->code.' ('.Text::plural($items->count(), 'code').')');

            return ['name' => $name, 'text' => $text, 'message' => 'Saved '.$name.'.'];
        }
        $file = $this->batchFile($batch);
        $this->log('downloaded '.$batch->file_name.($file['rebuilt'] ? ' (built again from the batch: the saved copy could not be read)' : ''));

        return ['name' => $batch->file_name, 'text' => $file['text'], 'rebuilt' => $file['rebuilt'], 'message' => 'Saved '.$batch->file_name.'.'];
    }

    public function fileText(string $code): array
    {
        $batch = SlcAttendanceBatch::where('code', $code)->whereNotNull('path')->first() ?? $this->fail('That batch has no file yet.');
        $file = $this->batchFile($batch);

        return ['name' => $batch->file_name, 'text' => $file['text'], 'rebuilt' => $file['rebuilt']];
    }

    /**
     * Regenerate file (user, 13 Nov 2026, as Bulk registration's batches): build the batch's file again from its
     * lines, dated as before or today, save it over the saved copy and hand it back. SIS refuses a file whose
     * content it has already imported, so a resubmission needs today's date (a new header).
     */
    public function regenerate(string $code, bool $today): array
    {
        $batch = SlcAttendanceBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status === 'awaiting') {
            $this->fail('Download the SIS file first.');
        }
        $date = $today ? now() : \Carbon\Carbon::parse($batch->file_date ?: now());
        $text = $this->buildBatchText($batch, $date);
        $name = $batch->file_name ?: AttendanceFile::fileName(explode('~', $batch->header)[0], (string) $batch->term, $batch->code);
        $disk = FileDisk::name();
        $path = 'slc-registration/attendance-batches/'.$name;
        try {
            $saved = (bool) Storage::disk($disk)->put($path, $text);
        } catch (\Throwable $e) {
            $saved = false;
        }
        if (!$saved) {
            \Illuminate\Support\Facades\Log::warning('SLC attendance: the batch file '.$path.' could not be saved to the '.$disk.' disk.');
        }
        $batch->update(['file_name' => $name, 'disk' => $disk, 'path' => $path, 'file_date' => $date->toDateString()]);
        $lines = substr_count($text, "\r\n") - 1;
        $this->log('regenerated SIS file '.$name.' for batch '.$batch->code.' ('.Text::plural($lines, 'line').', dated '.$date->format('d/m/Y').')');

        return ['name' => $name, 'text' => $text, 'rebuilt' => false,
            'message' => 'Regenerated '.$name.' ('.Text::plural($lines, 'student').', dated '.$date->format('d/m/Y').').'.($saved ? '' : ' It could not be saved to storage; it is built again when needed.')];
    }

    /** The batch's file: its students' lines with their confirmed codes (AttendanceFile::build), dated $date. */
    private function buildBatchText(SlcAttendanceBatch $batch, \DateTimeInterface $date): string
    {
        $items = SlcAttendanceImportRow::where('slc_attendance_batch_id', $batch->id)->orderBy('confirmed_at')->orderBy('id')->get();
        if ($items->isEmpty()) {
            $this->fail('The batch is empty.');
        }
        $bad = $items->reject(fn ($it) => AttendanceCodes::importable($it->confirmed_code))->count();
        if ($bad) {
            $this->fail(Text::plural($bad, 'line').' in this batch '.($bad === 1 ? 'has a code' : 'have codes').' SLC’s import doesn’t accept (only A, C, D, F, L, N, S, X). Remove '.($bad === 1 ? 'it' : 'them').' from the batch first.');
        }
        // spec v9.0 §6.12: one row per student, no duplicated rows, at most 50,000 students
        $twice = $items->countBy('ssn')->filter(fn ($n) => $n > 1)->keys();
        if ($twice->isNotEmpty()) {
            $this->fail('SLC takes one line per student, but '.$twice->implode(', ').' '.($twice->count() === 1 ? 'is' : 'are').' in this batch twice. Remove the extra line first.');
        }
        if ($items->count() > AttendanceFile::MAX_RECORDS) {
            $this->fail('SLC’s import takes at most 50,000 students a file.');
        }
        $lines = [];
        foreach ($items as $it) {
            $lines[$it->raw] = $it->confirmed_code;
        }

        return AttendanceFile::build($batch->header, $lines, $date);
    }

    /**
     * Why SLC would refuse D (never attended) for this line, or null (spec v9.0 §7.2.3): not for a student
     * who is Registered or Not Registered for the year (our Yes / No / N/R registration on the active course),
     * nor once their first liability period is confirmed as in attendance (a code A C F L N confirmed here for
     * the year, or on their SLC tab).
     */
    /** The student's SLC registration for the year on the active course: registered (Yes), not (No, N/R) or none. */
    private function registrationStatus(SlcAttendanceImportRow $row): string
    {
        $yearId = $row->import?->academic_year_id;
        $crel = (StudentSnapshots::load([(int) $row->student_id], $yearId)[(int) $row->student_id] ?? [])['crel_id'] ?? null;
        $statuses = $yearId && $crel ? DB::table('slc_registrations')->where('student_id', $row->student_id)->where('academic_year_id', $yearId)
            ->where('student_course_relation_id', $crel)->whereNull('deleted_at')->pluck('slc_registration_status_id')->map(fn ($v) => (int) $v)->all() : [];

        return in_array(1, $statuses, true) ? 'yes' : (array_intersect([2, 4], $statuses) ? 'not' : 'none');
    }

    private function neverAttendedRefusal(?SlcAttendanceImportRow $row): ?string
    {
        if (!$row || !$row->student_id) {
            return 'Match the student first.';
        }
        $yearId = $row->import?->academic_year_id;
        $crel = (StudentSnapshots::load([(int) $row->student_id], $yearId)[(int) $row->student_id] ?? [])['crel_id'] ?? null;
        $reg = $this->registrationStatus($row);
        if ($reg !== 'none') {
            return 'SLC won’t take D (never attended) for a student recorded as '.($reg === 'yes' ? 'Registered' : 'Not Registered').' for the year. Use X (not in attendance), or record it in SIS by hand.';
        }
        // (spec §7.2.3) nor when the year's liability periods aren't all with this course — e.g. after a transfer
        $elsewhere = $yearId && $crel && (DB::table('slc_registrations')->where('student_id', $row->student_id)->where('academic_year_id', $yearId)
            ->where('student_course_relation_id', '!=', $crel)->whereNull('deleted_at')->exists()
            || DB::table('slc_attendances as a')->join('slc_registrations as g', 'g.id', '=', 'a.slc_registration_id')->where('a.student_id', $row->student_id)
                ->where('g.academic_year_id', $yearId)->where('a.student_course_relation_id', '!=', $crel)->whereNull('a.deleted_at')->exists());
        if ($elsewhere) {
            return 'SLC won’t take D (never attended) when the year’s liability periods aren’t all on this course – the student has an SLC record for the year on another course (a transfer?). Record it in SIS by hand.';
        }
        $confirmedHere = $yearId && SlcAttendanceImportRow::where('student_id', $row->student_id)->where('id', '!=', $row->id)
            ->whereIn('confirmed_code', AttendanceCodes::IN_ATTENDANCE)->whereHas('import', fn ($q) => $q->where('academic_year_id', $yearId))->exists();
        $codeIds = DB::table('attendance_codes')->whereIn('code', AttendanceCodes::IN_ATTENDANCE)->whereNull('deleted_at')->pluck('id');
        $onSlcTab = $yearId && $crel && DB::table('slc_attendances as a')->join('slc_registrations as g', 'g.id', '=', 'a.slc_registration_id')
            ->where('a.student_course_relation_id', $crel)->where('g.academic_year_id', $yearId)->whereIn('a.attendance_code_id', $codeIds)
            ->whereNull('a.deleted_at')->exists();

        return $confirmedHere || $onSlcTab ? 'SLC won’t take D (never attended) once the student’s attendance is confirmed for the year. Use X (not in attendance) instead.' : null;
    }

    /**
     * The saved file, or — when it can't be read (a database copied from another server, a save that
     * failed) — built again from the batch's lines with its file date, which gives the same file.
     */
    private function batchFile(SlcAttendanceBatch $batch): array
    {
        $text = '';
        if ($batch->path) {
            try {
                $text = (string) Storage::disk($batch->disk ?: FileDisk::name())->get($batch->path);
            } catch (\Throwable $e) {
                $text = '';
            }
        }

        return $text !== ''
            ? ['text' => $text, 'rebuilt' => false]
            : ['text' => $this->buildBatchText($batch, \Carbon\Carbon::parse($batch->file_date ?: now())), 'rebuilt' => true];
    }

    public function markUploaded(string $code): string
    {
        $batch = SlcAttendanceBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status !== 'downloaded') {
            $this->fail($batch->status === 'uploaded' ? 'That batch is already marked uploaded.' : 'Download the SIS file first.');
        }
        $batch->update(['status' => 'uploaded', 'uploaded_by' => $this->user(), 'uploaded_at' => now()]);
        $n = SlcAttendanceImportRow::where('slc_attendance_batch_id', $batch->id)->count();
        $this->log('marked batch '.$batch->code.' as uploaded to SIS ('.Text::plural($n, 'student').')');

        return 'Batch '.$batch->code.' marked uploaded to SIS.';
    }

    /* ------------------------------------------------ Rejected by SIS (user, 13 Nov 2026) */

    /**
     * Lines of an uploaded batch that SIS rejected (its Attendance Confirmation Import Results, spec v9.0 §7.3): they
     * leave the batch and go back to step 4 with their code, to be confirmed into a new batch and sent again. The
     * batch keeps them listed as rejected (its saved file stays as sent); a newer upload's copy of the line, which
     * showed the batch as its earlier one, is freed with it. A line already recorded on the SLC tab can't be.
     */
    public function markRejected(string $code, array $rowIds, string $reason): string
    {
        $batch = SlcAttendanceBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status !== 'uploaded') {
            $this->fail('Only a batch uploaded to SIS has results to record.');
        }
        $rows = SlcAttendanceImportRow::where('slc_attendance_batch_id', $batch->id)->whereIn('id', $rowIds)->whereNull('slc_recorded')->get();
        if ($rows->isEmpty()) {
            $this->fail('Tick students of this batch whose attendance isn’t recorded on the SLC tab.');
        }
        $reason = trim($reason);
        DB::transaction(function () use ($rows, $batch, $reason) {
            foreach ($rows as $row) {
                $mark = ['sis_rejected_batch_id' => $batch->id, 'sis_rejected_code' => $row->confirmed_code, 'sis_rejected_reason' => $reason ?: null,
                    'sis_rejected_by' => $this->user(), 'sis_rejected_at' => now()];
                $free = ['confirmed_code' => null, 'confirmed_by' => null, 'confirmed_at' => null, 'slc_attendance_batch_id' => null, 'earlier_batch_id' => null];
                $row->update($free + $mark + ['sis_rejected_item' => true]);
                SlcAttendanceImportRow::where('ssn', $row->ssn)->where('id', '!=', $row->id)->where('earlier_batch_id', $batch->id)
                    ->get()->each(fn ($copy) => $copy->update($free + $mark + ['sis_rejected_item' => false]));
            }
        });
        $who = $rows->count() === 1 ? $this->who($rows->first()) : Text::plural($rows->count(), 'student');
        $this->log('recorded '.$who.' as rejected by SIS in batch '.$batch->code.($reason !== '' ? ' ('.$reason.')' : '').' – back to step 4 to send again');

        return $who.' back on step 4 to be confirmed into a new batch.';
    }

    /** A line marked rejected by mistake goes back into its batch — while it hasn't been confirmed again. */
    public function undoRejected(int $rowId): string
    {
        $row = SlcAttendanceImportRow::where('id', $rowId)->where('sis_rejected_item', true)->first() ?? $this->fail('That line isn’t marked rejected.');
        $batchId = $row->sis_rejected_batch_id;
        $copies = SlcAttendanceImportRow::where('ssn', $row->ssn)->where('id', '!=', $row->id)->where('sis_rejected_batch_id', $batchId)->where('sis_rejected_item', false)->get();
        if ($row->confirmed_code || $copies->contains(fn ($c) => $c->confirmed_code)) {
            $this->fail('It has been confirmed again since – remove it from the new batch first.');
        }
        $clear = ['sis_rejected_batch_id' => null, 'sis_rejected_item' => false, 'sis_rejected_code' => null, 'sis_rejected_reason' => null, 'sis_rejected_by' => null, 'sis_rejected_at' => null];
        $back = ['confirmed_code' => $row->sis_rejected_code, 'confirmed_by' => $this->user(), 'confirmed_at' => now()];
        DB::transaction(function () use ($row, $copies, $clear, $back, $batchId) {
            $row->update($back + ['slc_attendance_batch_id' => $batchId] + $clear);
            $copies->each(fn ($c) => $c->update($back + ['earlier_batch_id' => $batchId] + $clear));
        });
        $code = SlcAttendanceBatch::find($batchId)?->code;
        $this->log('took back the SIS rejection of '.$this->who($row).' – back in batch '.$code);

        return $this->who($row).' is back in batch '.$code.'.';
    }

    /* ------------------------------------------------ Bulk Attendance & Installment (user, 13 Nov 2026) */

    /**
     * What Bulk Attendance & Installment records for each line of a batch — and what the batch's student list
     * shows beforehand (user, 13 Nov 2026) — filled as staff fill them by hand:
     *   attendance (SLC tab › Add Attendance): the year's SLC registration (Yes, active course), confirmation
     *     date = the day the batch was marked uploaded, the liability period's term and session term
     *     (AttendanceState::liability: the earliest period without an attendance), the confirmed code,
     *     attendance year = the registration year; the CoC the code needed (queued on confirming) is linked;
     *   instalment (Accounts tab), for a fee-paying code only (A, L — spec v9.0 Appendix A): SLC pays the
     *     Tuition Fee Loan in three — 25% for term 1, 25% for term 2, 50% for term 3 — of the line's TFL
     *     (field 11), not the course fee; on the registration's agreement (slc_installments needs one).
     * D (never attended) has no registration: the attendance is recorded without one. A line already recorded
     * shows what was saved.
     *
     * @return array<int, array> by row id
     */
    private function plans(SlcAttendanceBatch $batch, ?array $rowIds = null): array
    {
        $q = SlcAttendanceImportRow::where('slc_attendance_batch_id', $batch->id)->orderBy('confirmed_at')->orderBy('id');
        $rows = ($rowIds === null ? $q : $q->whereIn('id', $rowIds))->get();
        if ($rows->isEmpty()) {
            return [];
        }
        $open = $rows->whereNull('slc_recorded');
        [$students, $periods] = $open->isNotEmpty() ? app(AttendanceState::class)->periodsFor($open, $batch->academic_year_id) : [[], []];
        $date = $batch->uploaded_at?->toDateString();
        $codeIds = DB::table('attendance_codes')->whereNull('deleted_at')->pluck('id', 'code')->mapWithKeys(fn ($id, $c) => [strtoupper($c) => (int) $id])->all();
        $notReg = AttendanceState::notRegistered($students, $batch->academic_year_id);
        $out = [];
        foreach ($rows as $row) {
            $out[$row->id] = $row->slc_recorded ? $this->recordedPlan($row)
                : $this->plan($row, $students[(int) $row->student_id] ?? null, $periods[(int) $row->student_id] ?? null, $date, $codeIds, $notReg[(int) $row->student_id] ?? null);
        }

        return $out;
    }

    private function plan(SlcAttendanceImportRow $row, ?array $s, ?array $l, ?string $date, array $codeIds, ?array $notReg = null): array
    {
        $code = strtoupper((string) $row->confirmed_code);
        // a Not Registered student's X or S (spec §7.2.3) is recorded against that registration, with no instalment
        $reg = ($s['registration'] ?? null) ?: ($notReg && in_array($code, AttendanceCodes::NOT_REGISTERED, true) ? $notReg : null);
        $tfl = (float) ($row->data['tfl'] ?? 0);
        $p = ['rowId' => $row->id, 'recorded' => false, 'status' => null, 'problem' => null, 'attendance' => null, 'installment' => null, 'noInstallment' => null,
            'taken' => [], 'moved' => false, 'tfl' => $tfl, 'row' => $row, 's' => $s];
        $p['problem'] = !$s ? 'student not matched' : (!$s['crel_id'] ? 'no active course' : (!$s['instance'] ? 'no course instance for the year'
            : (!isset($codeIds[$code]) ? 'code '.$code.' is not in the attendance code list'
            : (!$reg && $code !== 'D' ? 'no SLC registration recorded for the year' : null))));
        if (!$s || !$s['crel_id'] || !$s['instance']) {
            return $p;
        }

        // the year's Term 1–3 (course instance), and what the SLC tab / Accounts tab already have for each
        $terms = DB::table('instance_terms as t')->join('term_declarations as d', 'd.id', '=', 't.term_declaration_id')
            ->where('t.course_creation_instance_id', $s['instance']['id'])->whereNull('t.deleted_at')->whereNotNull('t.session_term')
            ->orderBy('t.session_term')->get(['t.session_term', 'd.id', 'd.name'])->keyBy(fn ($t) => (int) $t->session_term);
        if ($terms->isEmpty() && $l && $l['termDeclarationId'] && $l['sessionTerm']) {
            $terms = collect([(int) $l['sessionTerm'] => (object) ['session_term' => $l['sessionTerm'], 'id' => $l['termDeclarationId'], 'name' => $l['decl']]]); // class plans
        }
        $termOfDecl = $terms->mapWithKeys(fn ($t) => [(int) $t->id => (int) $t->session_term]);
        $agr = $reg ? DB::table('slc_agreements')->where('slc_registration_id', $reg['id'])->where('year', $reg['year'])->where('student_id', $s['id'])
            ->where('student_course_relation_id', $s['crel_id'])->whereNull('deleted_at')->orderBy('id')->first(['id', 'year']) : null;
        $atts = DB::table('slc_attendances as a')->leftJoin('attendance_codes as c', 'c.id', '=', 'a.attendance_code_id')->where('a.student_id', $s['id'])->whereNull('a.deleted_at')
            ->where(function ($q) use ($s, $reg, $termOfDecl) {
                $q->where(fn ($w) => $w->where('a.student_course_relation_id', $s['crel_id'])->whereIn('a.term_declaration_id', $termOfDecl->keys()->all() ?: [0]));
                if ($reg) {
                    $q->orWhere('a.slc_registration_id', $reg['id']);
                }
            })->orderBy('a.id')->get(['a.id', 'a.session_term', 'a.term_declaration_id', 'a.confirmation_date', 'a.created_by', 'c.code']);
        $insts = DB::table('slc_installments')->where('student_id', $s['id'])->whereNull('deleted_at')
            ->where(function ($q) use ($s, $agr, $termOfDecl) {
                $q->where(fn ($w) => $w->where('student_course_relation_id', $s['crel_id'])->whereIn('term_declaration_id', $termOfDecl->keys()->all() ?: [0]));
                if ($agr) {
                    $q->orWhere('slc_agreement_id', $agr->id);
                }
            })->orderBy('id')->get(['id', 'session_term', 'term_declaration_id', 'installment_date', 'amount', 'slc_money_receipt_id']);
        $termNo = fn ($x) => (int) ($x->session_term ?: ($termOfDecl[(int) $x->term_declaration_id] ?? 0));
        $names = RegistrationState::names($atts->pluck('created_by')->all());
        $day = fn ($d) => $d ? substr((string) $d, 0, 10) : null;

        // (user, 13 Nov 2026) no overwriting: a term already on the SLC tab is passed over and the line is recorded as
        // a new attendance (and instalment) for the year's first term that has none — Term 1 there → Term 2, …
        $taken = [];
        foreach (RecordTerm::SESSIONS as $n) {
            $a = $atts->first(fn ($x) => $termNo($x) === $n);
            if (!$a) {
                continue;
            }
            $i = $insts->first(fn ($x) => $termNo($x) === $n);
            $taken[$n] = ['sessionTerm' => $n, 'term' => $terms[$n]->name ?? null,
                'attendance' => ['id' => (int) $a->id, 'code' => $a->code, 'date' => $day($a->confirmation_date), 'by' => $names[$a->created_by] ?? ''],
                'installment' => $i ? ['id' => (int) $i->id, 'amount' => (float) $i->amount, 'date' => $day($i->installment_date), 'receiptId' => $i->slc_money_receipt_id ? (int) $i->slc_money_receipt_id : null] : null];
        }
        // the Session Term and Attendance Term: as chosen on step 4, else the suggested one (RecordTerm)
        $termList = $terms->map(fn ($t) => ['id' => (int) $t->id, 'name' => $t->name])->all();
        $res = RecordTerm::resolve($termList, RecordTerm::suggest($termList, array_keys($taken)), $row->record_session_term ? (int) $row->record_session_term : null,
            $row->record_term_declaration_id ? (int) $row->record_term_declaration_id : null);
        $period = $res['sessionTerm'];
        $p['taken'] = array_values($taken);
        $p['chosen'] = $res['chosen'];
        $p['moved'] = !$res['chosen'] && $period && $taken && $period > min(array_keys($taken)) && $l && (int) $l['sessionTerm'] !== $period;
        if (!$period) {
            $p['problem'] ??= 'Terms 1–3 of '.($reg ? 'Year '.$reg['year'] : 'the year').' are already on the SLC tab';

            return $p;
        }
        if (isset($taken[$period])) {
            $p['problem'] ??= 'Term '.$period.' is already on the SLC tab (#'.$taken[$period]['attendance']['id'].') – choose a free Session Term on step 4';

            return $p;
        }
        if (!$res['termDeclarationId']) {
            $p['problem'] ??= 'no Term '.$period.' on the course instance – choose the Attendance Term on step 4'.($taken ? ' (Term '.implode(', ', array_keys($taken)).' already on the SLC tab)' : '');

            return $p;
        }
        $t = (object) ['id' => $res['termDeclarationId'], 'name' => $res['term']];
        $p['attendance'] = [
            'registrationId' => $reg['id'] ?? null, 'registrationYear' => $reg['year'] ?? null, 'date' => $date,
            'termDeclarationId' => (int) $t->id, 'term' => $t->name, 'sessionTerm' => $period,
            'code' => $code, 'codeId' => $codeIds[$code] ?? null, 'attendanceYear' => $reg['year'] ?? ((int) ($s['instance']['rank'] ?? 0) ?: null),
        ];

        // the instalment: fee-paying codes only, a share of the TFL by the term — 25%, 25%, 50%
        $already = $insts->first(fn ($x) => $termNo($x) === $period);
        if (!in_array($code, AttendanceCodes::FEE_PAYING, true)) {
            $p['noInstallment'] = $code.' releases no tuition fee payment';
        } elseif ($tfl <= 0) {
            $p['noInstallment'] = 'No Tuition Fee Loan on SLC’s line, so SLC pays the college nothing';
        } elseif ($already) {
            $p['noInstallment'] = 'Term '.$period.' instalment already on Accounts (#'.$already->id.' · '.Text::money($already->amount).')'; // not added twice
        } elseif (!isset(AttendanceCodes::PERIOD_SHARE[$period])) {
            $p['noInstallment'] = 'No instalment share for Term '.$period.' (25%, 25%, 50% over Terms 1–3)';
        } elseif ($reg) {
            $share = AttendanceCodes::PERIOD_SHARE[$period];
            $p['installment'] = [
                'agreementId' => $agr ? (int) $agr->id : null, 'agreementYear' => $agr ? (int) $agr->year : null, 'date' => $date,
                'termDeclarationId' => (int) $t->id, 'term' => $t->name, 'sessionTerm' => $period,
                'amount' => round($tfl * $share, 2), 'share' => (int) round($share * 100),
            ];
            if (!$agr) {
                $p['problem'] ??= 'no SLC agreement for Year '.$reg['year'].' on the Accounts tab, needed for the instalment';
            }
        }

        return $p;
    }

    /** A line already recorded: the attendance and instalment it saved. */
    private function recordedPlan(SlcAttendanceImportRow $row): array
    {
        $a = $row->slc_attendance_id ? DB::table('slc_attendances as a')->leftJoin('term_declarations as d', 'd.id', '=', 'a.term_declaration_id')
            ->leftJoin('attendance_codes as c', 'c.id', '=', 'a.attendance_code_id')->leftJoin('slc_registrations as g', 'g.id', '=', 'a.slc_registration_id')
            ->where('a.id', $row->slc_attendance_id)->first(['a.id', 'a.slc_registration_id', 'g.registration_year', 'a.confirmation_date', 'a.term_declaration_id', 'd.name', 'a.session_term', 'c.code', 'a.deleted_at']) : null;
        $i = $row->slc_installment_id ? DB::table('slc_installments as i')->leftJoin('term_declarations as d', 'd.id', '=', 'i.term_declaration_id')
            ->leftJoin('slc_agreements as g', 'g.id', '=', 'i.slc_agreement_id')
            ->where('i.id', $row->slc_installment_id)->first(['i.id', 'i.slc_agreement_id', 'g.year', 'i.installment_date', 'i.term_declaration_id', 'd.name', 'i.session_term', 'i.amount', 'i.deleted_at']) : null;
        $day = fn ($d) => $d ? substr((string) $d, 0, 10) : null;

        return ['rowId' => $row->id, 'recorded' => true, 'status' => $row->slc_recorded, 'problem' => null, 'taken' => [], 'moved' => false, 'tfl' => (float) ($row->data['tfl'] ?? 0),
            'attendance' => $a ? ['id' => (int) $a->id, 'registrationId' => $a->slc_registration_id ? (int) $a->slc_registration_id : null, 'registrationYear' => $a->registration_year ? (int) $a->registration_year : null,
                'date' => $day($a->confirmation_date), 'termDeclarationId' => (int) $a->term_declaration_id, 'term' => $a->name, 'sessionTerm' => (int) $a->session_term, 'code' => $a->code, 'deleted' => (bool) $a->deleted_at] : null,
            'installment' => $i ? ['id' => (int) $i->id, 'agreementId' => (int) $i->slc_agreement_id, 'agreementYear' => $i->year ? (int) $i->year : null, 'date' => $day($i->installment_date),
                'termDeclarationId' => (int) $i->term_declaration_id, 'term' => $i->name, 'sessionTerm' => (int) $i->session_term, 'amount' => (float) $i->amount, 'deleted' => (bool) $i->deleted_at] : null,
            'noInstallment' => $i ? null : (in_array(strtoupper((string) $row->confirmed_code), AttendanceCodes::FEE_PAYING, true) ? 'No instalment was added' : strtoupper((string) $row->confirmed_code).' releases no tuition fee payment'),
        ];
    }

    /** Batches › View students: each line's attendance and instalment, to be recorded or recorded (user, 13 Nov 2026). */
    public function batchPreview(string $code): array
    {
        $batch = SlcAttendanceBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        $rows = [];
        $expected = 0.0;
        $paid = 0.0;
        $n = ['toRecord' => 0, 'moved' => 0, 'problem' => 0];
        foreach ($this->plans($batch) as $id => $p) {
            unset($p['row'], $p['s']);
            $amount = (float) ($p['installment']['amount'] ?? 0);
            if ($p['recorded']) {
                $paid += empty($p['installment']['deleted']) ? $amount : 0;
            } elseif ($p['problem']) {
                $n['problem']++;
            } else {
                $expected += $amount;
                $n['toRecord']++;
                $n['moved'] += $p['moved'] ? 1 : 0; // recorded as a later term: the one asked about is already there
            }
            $rows[$id] = $p;
        }

        return ['code' => $batch->code, 'date' => $batch->uploaded_at?->toDateString(), 'rows' => $rows, 'expected' => round($expected, 2), 'recorded' => round($paid, 2)] + $n;
    }

    /**
     * Bulk Attendance & Installment: for the ticked students of a batch uploaded to SIS, a new attendance on the
     * SLC tab and, for a fee-paying code, a new instalment on the Accounts tab, as their plan shows (plans()).
     * Nothing already there is changed (user, 13 Nov 2026): a term already on the SLC tab is passed over for the
     * year's next free term (Term 1 there → Term 2). A student the plan can't record is skipped with the reason.
     */
    public function bulkAttendance(string $code, array $rowIds): string
    {
        $batch = SlcAttendanceBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status !== 'uploaded') {
            $this->fail('Mark the batch uploaded to SIS first.');
        }
        $all = $this->plans($batch, $rowIds);
        $plans = array_filter($all, fn ($p) => !$p['recorded']);
        if (!$plans) {
            $this->fail($all ? 'Those students’ attendance is already recorded.' : 'Tick at least one student of this batch.');
        }
        $done = [];
        $moved = [];
        $skipped = [];
        $paid = [0, 0.0]; // instalments added, their total
        $note = 'Confirmed in bulk (SLC attendance batch '.$batch->code.')';

        DB::transaction(function () use ($plans, $note, &$done, &$moved, &$skipped, &$paid) {
            foreach ($plans as $p) {
                $row = $p['row'];
                $s = $p['s'];
                $who = $s['reg_no'] ?? $row->ssn;
                if ($p['problem']) {
                    $skipped[] = $who.' ('.$p['problem'].')';
                    continue;
                }
                $a = $p['attendance'];
                $att = \App\Models\SlcAttendance::create([
                    'student_id' => $s['id'], 'course_relation_id' => $s['course_creation_id'], 'student_course_relation_id' => $s['crel_id'],
                    'course_creation_instance_id' => $s['instance']['id'], 'slc_registration_id' => $a['registrationId'] ?? 0, 'confirmation_date' => $a['date'],
                    'attendance_year' => $a['attendanceYear'], 'term_declaration_id' => $a['termDeclarationId'], 'session_term' => $a['sessionTerm'],
                    'attendance_code_id' => $a['codeId'], 'note' => $note, 'created_by' => $this->user(),
                ]);
                if ($row->slc_coc_id) {
                    \App\Models\SlcCoc::where('id', $row->slc_coc_id)->whereNull('slc_attendance_id')->update(['slc_attendance_id' => $att->id]);
                }
                $i = $p['installment'];
                $installment = $i ? \App\Models\SlcInstallment::create([
                    'student_id' => $s['id'], 'student_course_relation_id' => $s['crel_id'], 'course_creation_instance_id' => $s['instance']['id'],
                    'slc_attendance_id' => $att->id, 'slc_agreement_id' => $i['agreementId'], 'installment_date' => $i['date'], 'amount' => $i['amount'],
                    'session_term' => $i['sessionTerm'], 'term_declaration_id' => $i['termDeclarationId'], 'created_by' => $this->user(),
                ]) : null;
                if ($installment) {
                    $paid = [$paid[0] + 1, $paid[1] + (float) $i['amount']];
                }
                $row->update(['slc_attendance_id' => $att->id, 'slc_installment_id' => $installment?->id, 'slc_recorded' => 'created', 'slc_recorded_by' => $this->user(), 'slc_recorded_at' => now()]);
                $done[] = $who.' (Term '.$a['sessionTerm'].')';
                if ($p['moved']) {
                    $moved[] = $who.': Term '.implode(', ', array_column($p['taken'], 'sessionTerm')).' already there → Term '.$a['sessionTerm'];
                }
            }
        });

        $inst = $paid[0] ? ', with '.Text::plural($paid[0], 'instalment').' ('.Text::money($paid[1]).')' : '';
        $this->log('Bulk Attendance & Installment for batch '.$batch->code.': '.($done ? Text::plural(count($done), 'SLC attendance').' recorded ('.implode(', ', $done).')'.$inst : 'nothing recorded')
            .($moved ? '; recorded as the next free term: '.implode('; ', $moved) : '').($skipped ? '; not recorded: '.implode(', ', $skipped) : ''));

        return ($done ? 'Recorded for '.Text::plural(count($done), 'student').$inst : 'Nothing recorded')
            .($moved ? ' ('.count($moved).' as the next free term – the earlier term was already on the SLC tab)' : '')
            .'.'.($skipped ? ' Not recorded: '.implode(', ', $skipped).'.' : '');
    }

    /* ------------------------------------------------------- carry-over */

    /**
     * A newer upload must not lose the work on the earlier one (as for Bulk registration): each line
     * takes over the most recent earlier line with the same SSN for the same academic year and term —
     * the SSN saved here, the field decisions while SLC still sends the same value (the same CoC),
     * the chosen code, and the confirmation: one waiting in the batch moves to the new line (the batch
     * then sends the new line), one already downloaded stays "Earlier batch".
     *
     * @return int students that had something to carry
     */
    private function carryOver(SlcAttendanceImport $import): int
    {
        $rows = SlcAttendanceImportRow::where('slc_attendance_import_id', $import->id)->get();
        $prevBySsn = SlcAttendanceImportRow::with('fields')->whereIn('ssn', $rows->pluck('ssn'))->where('slc_attendance_import_id', '<', $import->id)
            ->whereIn('slc_attendance_import_id', SlcAttendanceImport::where('academic_year_id', $import->academic_year_id)->pluck('id'))
            ->orderBy('slc_attendance_import_id')->orderBy('id')->get()
            ->groupBy(fn ($p) => $p->ssn.'|'.strtoupper((string) ($p->data['term'] ?? '')))->map->last();
        if ($prevBySsn->isEmpty()) {
            return 0;
        }
        $batches = SlcAttendanceBatch::whereIn('id', $prevBySsn->flatMap(fn ($p) => [$p->slc_attendance_batch_id, $p->earlier_batch_id])->filter()->unique())->get()->keyBy('id');
        $n = 0;
        DB::transaction(function () use ($rows, $prevBySsn, $batches, $import, &$n) {
            foreach ($rows as $row) {
                $prev = $prevBySsn->get($row->ssn.'|'.strtoupper((string) ($row->data['term'] ?? '')));
                if (!$prev) {
                    continue;
                }
                $upd = ['carried_from_id' => $prev->id];
                $did = false;
                if ($prev->match_method === 'linked' && $row->student_id && (int) $row->student_id === (int) $prev->student_id) {
                    $upd += ['match_method' => 'linked', 'linked_by' => $prev->linked_by, 'linked_at' => $prev->linked_at];
                    $did = true;
                }
                if ($prev->attendance_code) {
                    $upd += ['attendance_code' => $prev->attendance_code, 'code_by' => $prev->code_by, 'code_at' => $prev->code_at];
                    $did = true;
                }
                if ($prev->record_session_term || $prev->record_term_declaration_id) { // step 4's chosen SLC tab term
                    $upd += ['record_session_term' => $prev->record_session_term, 'record_term_declaration_id' => $prev->record_term_declaration_id,
                        'record_term_by' => $prev->record_term_by, 'record_term_at' => $prev->record_term_at];
                    $did = true;
                }
                $batch = $batches->get($prev->slc_attendance_batch_id ?: $prev->earlier_batch_id);
                if ($prev->confirmed_code && $batch && (int) $row->student_id === (int) $prev->student_id) {
                    $upd += ['confirmed_code' => $prev->confirmed_code, 'confirmed_by' => $prev->confirmed_by, 'confirmed_at' => $prev->confirmed_at, 'slc_coc_id' => $prev->slc_coc_id];
                    if ($batch->status === 'awaiting' && $prev->slc_attendance_batch_id) {
                        $upd['slc_attendance_batch_id'] = $batch->id;
                        $prev->update(['slc_attendance_batch_id' => null, 'slc_coc_id' => null]);
                        if (explode('~', $batch->header)[0] === $import->hei) {
                            $batch->update(['header' => $import->header]);
                        }
                    } else {
                        $upd['earlier_batch_id'] = $batch->id;
                    }
                    $did = true;
                }
                foreach ($prev->fields as $f) {
                    $key = self::FIELD_DATA[$f->field] ?? null;
                    if ($key === null || ($prev->data[$key] ?? null) === ($row->data[$key] ?? null)) {
                        \App\Models\SlcAttendanceRowField::create(['slc_attendance_import_row_id' => $row->id, 'field' => $f->field, 'route' => $f->route, 'slc_coc_id' => $f->slc_coc_id, 'created_by' => $f->created_by]);
                        $did = true;
                    }
                }
                $row->update($upd);
                $n += $did ? 1 : 0;
            }
        });

        return $n;
    }

    /** The line's value a Data check field decision depends on (null: the decision doesn't depend on SLC's value). */
    private const FIELD_DATA = ['first' => 'forename', 'last' => 'surname', 'dob' => 'dob', 'ssn' => 'ssn', 'course' => 'slcCode', 'year' => 'year', 'registration' => null, 'fee' => 'fee', 'tfl' => 'tfl'];

    private function who(SlcAttendanceImportRow $row): string
    {
        $st = $row->student_id ? Student::find($row->student_id) : null;

        return $st ? trim($st->first_name.' '.$st->last_name) : 'SSN '.$row->ssn;
    }

    public function link(int $rowId, int $studentId): string
    {
        $row = SlcAttendanceImportRow::find($rowId) ?? $this->fail('That line is no longer in the upload.');
        if ($row->student_id) {
            $this->fail('This line is already matched to a student.');
        }
        $other = Student::where('ssn_no', $row->ssn)->where('id', '!=', $studentId)->first();
        if ($other) {
            $this->fail('SSN '.$row->ssn.' is already held on '.$other->registration_no.'.');
        }
        $st = Student::find($studentId) ?? $this->fail('Student not found.');
        if (trim((string) $st->ssn_no) !== '' && $st->ssn_no !== $row->ssn) {
            $this->fail($st->registration_no.' already has SSN '.$st->ssn_no.'.');
        }
        DB::transaction(function () use ($row, $st) {
            StudentArchive::create(['student_id' => $st->id, 'table' => 'students', 'field_name' => 'ssn_no', 'field_value' => $st->ssn_no, 'field_new_value' => $row->ssn, 'created_by' => $this->user()]);
            $st->ssn_no = $row->ssn;
            $st->save();
            $row->update(['student_id' => $st->id, 'match_method' => 'linked', 'linked_by' => $this->user(), 'linked_at' => now()]);
        });
        $this->log('matched '.$st->registration_no.' '.$st->first_name.' '.$st->last_name.' and saved SSN '.$row->ssn.' to their record');

        return 'SSN saved to '.$st->first_name.' '.$st->last_name.'.';
    }

    public function unlink(int $rowId): string
    {
        $row = SlcAttendanceImportRow::find($rowId) ?? $this->fail('That line is no longer in the upload.');
        if ($row->match_method !== 'linked') {
            $this->fail('Only a student matched here can be unmatched.');
        }
        $st = Student::find($row->student_id);
        DB::transaction(function () use ($row, $st) {
            if ($st && $st->ssn_no === $row->ssn) {
                StudentArchive::create(['student_id' => $st->id, 'table' => 'students', 'field_name' => 'ssn_no', 'field_value' => $st->ssn_no, 'field_new_value' => null, 'created_by' => $this->user()]);
                $st->ssn_no = null;
                $st->save();
            }
            $row->update(['student_id' => null, 'match_method' => null, 'linked_by' => null, 'linked_at' => null]);
        });
        $this->log('unmatched SSN '.$row->ssn.' from '.($st->registration_no ?? 'the student').' (SSN removed from the student record)');

        return 'SSN '.$row->ssn.' removed from '.trim(($st->first_name ?? '').' '.($st->last_name ?? '')).'.';
    }
}
