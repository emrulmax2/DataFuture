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

    /* ---------------------------------------------------- step 5: the code */

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
        $row->update(['attendance_code' => $code, 'code_by' => $code ? $this->user() : null, 'code_at' => $code ? now() : null]);
        $who = $this->who($row);
        $this->log($code ? 'chose code '.$code.' ('.(AttendanceCodes::MEANING[$code] ?? '').') for '.$who : 'put '.$who.' back to the suggested code');

        return $code ? 'Code '.$code.' chosen for '.$who.'.' : $who.' is back on the suggested code.';
    }

    /**
     * Confirm the code of each line that is ready (matched, an SLC registration recorded for the year —
     * user, 2 Nov 2026: without one the code must not be submitted — the Data check sorted, a code
     * chosen or suggested): it goes into the batch awaiting download for the academic year, HEI and
     * term, and a code that needs a CoC queues one on the student's SLC tab.
     */
    public function confirm(array $rowIds): string
    {
        $state = app(AttendanceState::class)->build();
        $asked = collect($state['rows'])->whereIn('id', $rowIds);
        $items = $asked->where('codeStage', 'ready')->values();
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
        $skipped = count(array_unique($rowIds)) - $items->count();

        return 'Confirmed '.$who.' into batch '.implode(', ', $batches).'.'.($cocs ? ' '.Text::plural($cocs, 'CoC').' queued.' : '').($skipped > 0 ? ' '.Text::plural($skipped, 'student').' not ready yet were left out.' : '');
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

    /* ------------------------------------------------------- step 6: CoCs */

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

    /* ---------------------------------------------------- step 7: batches */

    /** Generates the SIS attendance import file the first time (batch → downloaded); later calls return the same file. */
    public function download(string $code): array
    {
        $batch = SlcAttendanceBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status === 'awaiting') {
            $items = SlcAttendanceImportRow::where('slc_attendance_batch_id', $batch->id)->orderBy('confirmed_at')->orderBy('id')->get();
            if ($items->isEmpty()) {
                $this->fail('The batch is empty.');
            }
            $lines = [];
            foreach ($items as $it) {
                $lines[$it->raw] = $it->confirmed_code;
            }
            $text = AttendanceFile::build($batch->header, $lines, now());
            $name = AttendanceFile::fileName(explode('~', $batch->header)[0], (string) $batch->term, $batch->code);
            $disk = FileDisk::name();
            $path = 'slc-registration/attendance-batches/'.$name;
            Storage::disk($disk)->put($path, $text);
            $batch->update(['status' => 'downloaded', 'file_name' => $name, 'disk' => $disk, 'path' => $path, 'file_date' => now()->toDateString(), 'downloaded_by' => $this->user(), 'downloaded_at' => now()]);
            $this->log('generated SIS file '.$name.' for batch '.$batch->code.' ('.Text::plural($items->count(), 'code').')');

            return ['name' => $name, 'text' => $text, 'message' => 'Saved '.$name.'.'];
        }
        $this->log('downloaded '.$batch->file_name);

        return ['name' => $batch->file_name, 'text' => (string) Storage::disk($batch->disk)->get($batch->path), 'message' => 'Saved '.$batch->file_name.'.'];
    }

    public function fileText(string $code): array
    {
        $batch = SlcAttendanceBatch::where('code', $code)->whereNotNull('path')->first() ?? $this->fail('That batch has no file yet.');

        return ['name' => $batch->file_name, 'text' => (string) Storage::disk($batch->disk)->get($batch->path)];
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
