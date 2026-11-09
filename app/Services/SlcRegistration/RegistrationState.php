<?php

namespace App\Services\SlcRegistration;

use App\Models\AcademicYear;
use App\Models\SlcBulkActivity;
use App\Models\SlcBulkBatch;
use App\Models\SlcBulkImport;
use App\Models\SlcBulkImportRow;
use App\Models\SlcBulkUpdate;
use Illuminate\Support\Facades\DB;

/**
 * Everything the /slc-registration page shows, in one payload: the current upload (the newest),
 * each line with its student, checks, routes, awarding body, attendance, candidates and stage,
 * the batches, record updates, activity and the course mapping. The page renders it as the
 * prototype does; every action answers with a fresh copy.
 */
class RegistrationState
{
    private array $names = [];

    /** decided_at of the earlier lines that lines were carried from: a decision equal to it came across */
    private array $prevDecided = [];

    /** the remembered No actions of the upload's students (NoActionMemory), by student id */
    private ?\Illuminate\Support\Collection $noActions = null;

    public function build(): array
    {
        $import = SlcBulkImport::with('academicYear')->latest('id')->first();
        $codeMap = self::codeMap();
        $rows = $import ? SlcBulkImportRow::with('fields')->where('slc_bulk_import_id', $import->id)->orderBy('line_no')->get() : collect();
        $yearId = $import?->academic_year_id;
        $yearName = (string) ($import?->academicYear?->name ?? '');

        $students = StudentSnapshots::load($rows->pluck('student_id')->filter()->all(), $yearId);
        $this->prevDecided = SlcBulkImportRow::whereIn('id', $rows->pluck('carried_from_id')->filter())->whereNotNull('decided_at')->get(['id', 'decided_at'])
            ->mapWithKeys(fn ($p) => [$p->id => $p->decided_at->toDateTimeString()])->all();
        $pool = [];
        if ($rows->contains(fn ($r) => !$r->student_id && !$r->decision)) {
            $poolIds = DB::table('students')->whereNull('deleted_at')->where(fn ($q) => $q->whereNull('ssn_no')->orWhere('ssn_no', ''))->pluck('id')->all();
            $pool = StudentSnapshots::load($poolIds, $yearId);
        }
        $batches = SlcBulkBatch::orderBy('id')->get();
        $batchById = $batches->keyBy('id');
        $userIds = $rows->flatMap(fn ($r) => array_merge([$r->linked_by, $r->decided_by], $r->fields->pluck('state_by')->all(), array_column($r->history ?? [], 'by')))->filter()->all();
        $this->noActions = NoActionMemory::forStudents($rows->pluck('student_id')->all());
        $userIds = array_merge($userIds, $this->noActions->flatten(1)->pluck('created_by')->all());

        $info = [];
        foreach ($rows as $row) {
            $info[] = $this->row($row, $students[$row->student_id] ?? null, $pool, $codeMap, $yearName, $batchById);
        }
        $counts = $this->counts($info);

        $batchItems = SlcBulkImportRow::whereNotNull('slc_bulk_batch_id')->orderBy('decided_at')->orderBy('id')
            ->get(['id', 'slc_bulk_import_id', 'slc_bulk_batch_id', 'ssn', 'data', 'student_id', 'decided_by', 'decided_at', 'slc_registration_id', 'slc_agreement_id', 'slc_recorded', 'slc_recorded_by', 'slc_recorded_at']);
        $itemRecords = $this->itemRecords($batchItems);
        $itemStudents = DB::table('students')->whereIn('id', $batchItems->pluck('student_id')->filter()->unique())->get(['id', 'first_name', 'last_name', 'registration_no'])->keyBy('id');
        $statuses = StudentStatus::forIds(array_merge($batchItems->pluck('student_id')->all(), $rows->pluck('student_id')->all()));
        $userIds = array_merge($userIds, $batchItems->pluck('decided_by')->all(), $batches->flatMap(fn ($b) => [$b->created_by, $b->downloaded_by, $b->uploaded_by])->all());

        // changes made to these students' records from SLC data, on this upload or an earlier one
        $updates = $import ? SlcBulkUpdate::whereIn('student_id', $rows->pluck('student_id')->filter()->unique())->orderByDesc('id')->get() : collect();
        $log = SlcBulkActivity::orderByDesc('id')->limit(400)->get();
        $userIds = array_merge($userIds, $updates->pluck('created_by')->all(), $log->pluck('user_id')->all(), [$import?->created_by]);
        $this->names = self::names($userIds);
        $updateStudents = DB::table('students')->whereIn('id', $updates->pluck('student_id')->unique())->get(['id', 'first_name', 'last_name', 'registration_no'])->keyBy('id');
        // their mobile numbers (student_contacts, the newest row), shown on Actions › Our record updated
        $updateMobiles = DB::table('student_contacts')->whereIn('student_id', $updates->pluck('student_id')->unique())->whereNull('deleted_at')->orderBy('id')->get(['student_id', 'mobile'])->keyBy('student_id');

        return [
            'import' => $import ? [
                'id' => $import->id, 'fileName' => $import->file_name,
                'header' => SisFile::header($import->header) + ['yearName' => $yearName],
                'footer' => $import->footer, 'errors' => $import->error_lines ?? [],
                'by' => $this->name($import->created_by), 'at' => $this->when($import->created_at),
            ] : null,
            'rows' => $this->finishNames($info),
            'counts' => $counts,
            'batches' => $batches->map(fn ($b) => [
                'id' => $b->code, 'key' => $b->id, 'status' => $b->status, 'header' => $b->header,
                'items' => $batchItems->where('slc_bulk_batch_id', $b->id)->values()->map(fn ($it) => [
                    'rowId' => $it->id, 'r' => ['ssn' => $it->ssn],
                    'name' => trim(($itemStudents[$it->student_id]->first_name ?? '').' '.($itemStudents[$it->student_id]->last_name ?? '')),
                    'sid' => $itemStudents[$it->student_id]->registration_no ?? '', 'by' => $this->name($it->decided_by), 'at' => $this->when($it->decided_at),
                    'url' => $it->student_id ? route('student.show', $it->student_id) : null, 'status' => $statuses[$it->student_id] ?? null,
                    'course' => trim(($it->data['slcCode'] ?? '').' · Year '.($it->data['year'] ?? ''), ' ·'),
                    // Bulk SLC Registration: what was done for the line, or what the student already has
                    'record' => $itemRecords[$it->id],
                ])->all(),
                'file' => $b->path ? ['name' => $b->file_name] : null,
                'createdBy' => $this->name($b->created_by), 'createdAt' => $this->when($b->created_at),
                'downloadedBy' => $b->downloaded_by ? $this->name($b->downloaded_by) : null, 'downloadedAt' => $this->when($b->downloaded_at),
                'uploadedBy' => $b->uploaded_by ? $this->name($b->uploaded_by) : null, 'uploadedAt' => $this->when($b->uploaded_at),
            ])->all(),
            'updates' => $updates->map(fn ($u) => [
                'at' => $this->when($u->created_at), 'by' => $this->name($u->created_by), 'sid' => $updateStudents[$u->student_id]->registration_no ?? '', 'studentId' => (int) $u->student_id,
                'name' => trim(($updateStudents[$u->student_id]->first_name ?? '').' '.($updateStudents[$u->student_id]->last_name ?? '')),
                'field' => $u->field, 'from' => $u->from_value, 'to' => $u->to_value, 'status' => $statuses[$u->student_id] ?? null,
                'mobile' => trim((string) ($updateMobiles[$u->student_id]->mobile ?? '')),
            ])->all(),
            'log' => $log->map(fn ($e) => ['at' => $this->when($e->created_at), 'user' => $e->user_id ? $this->name($e->user_id) : 'System', 'text' => $e->text])->all(),
            'courses' => $this->courses($rows, $codeMap, $yearId),
            'dashboard' => $this->dashboard($import, $counts, $batches, $batchItems),
            // Bulk attendance
            'attendance' => app(AttendanceState::class)->build(),
            // Bulk payment upload (SLC remittance → money receipts)
            'payments' => app(PaymentState::class)->build(),
        ];
    }

    /**
     * The Dashboard tab: where each of the three processes stands, the year's SLC figures, and the
     * students with more than one agreement for the same year on the same course (user, 2 Nov 2026).
     * The year is the current upload's, else the academic year today falls in.
     */
    private function dashboard(?SlcBulkImport $import, array $counts, $batches, $batchItems): array
    {
        $year = $import?->academicYear ?? AcademicYear::where('from_date', '<=', now()->toDateString())->where('to_date', '>=', now()->toDateString())->orderByDesc('id')->first();
        $yearId = $year?->id;
        $from = $year?->from_date ? substr((string) $year->from_date, 0, 10) : null;
        $to = $year?->to_date ? substr((string) $year->to_date, 0, 10) : null;

        $regs = $yearId ? DB::table('slc_registrations')->where('academic_year_id', $yearId)->whereNull('deleted_at')->select('slc_registration_status_id', DB::raw('COUNT(*) as n'))->groupBy('slc_registration_status_id')->pluck('n', 'slc_registration_status_id') : collect();
        $statusNames = DB::table('slc_registration_statuses')->pluck('name', 'id');
        $agreements = $yearId ? DB::table('slc_agreements as a')->join('course_creation_instances as i', 'i.id', '=', 'a.course_creation_instance_id')->where('i.academic_year_id', $yearId)->whereNull('a.deleted_at')->count() : 0;
        $attendance = $yearId ? DB::table('slc_attendances as a')->join('slc_registrations as r', 'r.id', '=', 'a.slc_registration_id')->leftJoin('attendance_codes as c', 'c.id', '=', 'a.attendance_code_id')
            ->where('r.academic_year_id', $yearId)->whereNull('a.deleted_at')->whereNull('r.deleted_at')
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN c.coc_required = 1 THEN 1 ELSE 0 END) as coc')->first() : null;
        $cocs = ($from && $to) ? DB::table('slc_cocs')->whereNull('deleted_at')->whereBetween('confirmation_date', [$from, $to])
            ->selectRaw("SUM(CASE WHEN actioned = 'Yes' THEN 1 ELSE 0 END) as submitted, SUM(CASE WHEN actioned = 'Yes' THEN 0 ELSE 1 END) as open, SUM(CASE WHEN reason LIKE 'SLC registration check%' THEN 1 ELSE 0 END) as from_check")->first() : null;

        return [
            'year' => $year ? ['id' => $year->id, 'name' => $year->name] : null,
            'registration' => [
                'upload' => $import ? ['fileName' => $import->file_name, 'at' => $this->when($import->created_at), 'by' => $this->name($import->created_by)] : null,
                'counts' => $counts,
                'batches' => [
                    'awaiting' => $batches->where('status', 'awaiting')->count(), 'awaitingStudents' => $batchItems->whereIn('slc_bulk_batch_id', $batches->where('status', 'awaiting')->pluck('id'))->count(),
                    'downloaded' => $batches->where('status', 'downloaded')->count(), 'uploaded' => $batches->where('status', 'uploaded')->count(),
                ],
                'recorded' => $batchItems->whereNotNull('slc_recorded')->count(),
                'toRecord' => $batchItems->whereIn('slc_bulk_batch_id', $batches->where('status', 'uploaded')->pluck('id'))->whereNull('slc_recorded')->count(),
            ],
            'slc' => [
                'registrations' => (int) $regs->sum(),
                'byStatus' => $regs->map(fn ($n, $id) => ['name' => $statusNames[$id] ?? ('Status '.$id), 'n' => (int) $n])->values()->all(),
                'agreements' => $agreements,
                'attendances' => (int) ($attendance->total ?? 0), 'attendanceCoc' => (int) ($attendance->coc ?? 0),
                'cocsOpen' => (int) ($cocs->open ?? 0), 'cocsSubmitted' => (int) ($cocs->submitted ?? 0), 'cocsFromCheck' => (int) ($cocs->from_check ?? 0),
            ],
            // "Duplicate agreements": the box counts them and opens their list on its own page
            'duplicates' => ['count' => self::duplicateAgreementGroups()->count(), 'hidden' => self::duplicateAgreementGroups(true)->count(), 'url' => route('slc.registration.multiple')],
        ];
    }

    /**
     * Dashboard › "Duplicate agreements": a student with two or more agreements (not deleted) for the
     * same year — the agreements table's `year` (year of study) — on the same course relation.
     */
    public static function duplicateAgreementGroups(bool $hidden = false)
    {
        $groups = DB::table('slc_agreements')->whereNull('deleted_at')
            ->select('student_id', 'student_course_relation_id', 'year', DB::raw('COUNT(*) as n'), DB::raw('GROUP_CONCAT(id ORDER BY id) as ids'))
            ->groupBy('student_id', 'student_course_relation_id', 'year')->havingRaw('COUNT(*) > 1')->get();
        // "Reviewed & Hide" (user, 5 Nov 2026): a group stays hidden while it has exactly the agreements it had when reviewed
        $reviews = $groups->isEmpty() ? collect() : \App\Models\SlcAgreementDuplicateReview::whereIn('student_id', $groups->pluck('student_id')->unique())
            ->orderBy('id')->get()->keyBy(fn ($r) => self::groupKey($r->student_id, $r->student_course_relation_id, $r->year));
        foreach ($groups as $g) {
            $g->agreementIds = array_map('intval', explode(',', (string) $g->ids));
            $g->review = $reviews->get(self::groupKey($g->student_id, $g->student_course_relation_id, $g->year));
            $g->hidden = $g->review && $g->review->hidden && $g->review->agreement_ids == $g->agreementIds;
        }

        return $groups->filter(fn ($g) => $g->hidden === $hidden)->values();
    }

    public static function groupKey($studentId, $crelId, $year): string
    {
        return (int) $studentId.'|'.(int) $crelId.'|'.(int) $year;
    }

    /** Those students with their agreements, newest first — the list behind the box (/slc-registration/multiple-agreements); with $hidden, the ones reviewed & hidden. */
    public static function duplicateAgreements(bool $hidden = false): array
    {
        $groups = self::duplicateAgreementGroups($hidden);
        if ($groups->isEmpty()) {
            return [];
        }
        $agreements = DB::table('slc_agreements as a')
            ->leftJoin('course_creation_instances as i', 'i.id', '=', 'a.course_creation_instance_id')
            ->leftJoin('academic_years as y', 'y.id', '=', 'i.academic_year_id')
            ->leftJoin('slc_registrations as r', fn ($j) => $j->on('r.id', '=', 'a.slc_registration_id')->whereNull('r.deleted_at'))
            ->whereIn('a.student_id', $groups->pluck('student_id')->unique())->whereNull('a.deleted_at')
            ->orderBy('a.id')
            ->get(['a.id', 'a.student_id', 'a.student_course_relation_id', 'a.year', 'a.date', 'a.fees', 'a.total', 'a.slc_coursecode', 'a.created_by', 'a.created_at', 'y.name as instance_year', 'r.id as reg_id', 'r.confirmation_date as reg_date']);
        $students = DB::table('students')->whereIn('id', $groups->pluck('student_id')->unique())->get(['id', 'registration_no', 'first_name', 'last_name'])->keyBy('id');
        $courses = DB::table('student_course_relations as r')->join('course_creations as cc', 'cc.id', '=', 'r.course_creation_id')
            ->leftJoin('courses as c', 'c.id', '=', 'cc.course_id')->leftJoin('semesters as sm', 'sm.id', '=', 'cc.semester_id')
            ->whereIn('r.id', $groups->pluck('student_course_relation_id')->filter()->unique())->get(['r.id', 'c.name as course', 'sm.name as intake'])->keyBy('id');
        $names = self::names(array_merge($agreements->pluck('created_by')->all(), $groups->map(fn ($g) => $g->review->reviewed_by ?? null)->all()));
        $when = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('d/m/Y H:i') : '';

        $statuses = StudentStatus::forIds($groups->pluck('student_id')->all());

        return $groups->map(function ($g) use ($agreements, $students, $courses, $names, $when, $statuses) {
            $list = $agreements->filter(fn ($a) => (int) $a->student_id === (int) $g->student_id && (int) $a->student_course_relation_id === (int) $g->student_course_relation_id && (int) $a->year === (int) $g->year)->values();
            $st = $students[$g->student_id] ?? null;
            $course = $courses[$g->student_course_relation_id] ?? null;

            return [
                'studentId' => (int) $g->student_id, 'crelId' => $g->student_course_relation_id ? (int) $g->student_course_relation_id : null, 'agreementIds' => $g->agreementIds,
                'review' => $g->review ? ['by' => $names[$g->review->reviewed_by] ?? '', 'at' => $g->review->reviewed_at?->format('d/m/Y H:i'), 'hidden' => $g->hidden,
                    'changed' => !$g->hidden && $g->review->hidden] : null,
                'regNo' => $st->registration_no ?? '', 'name' => trim(($st->first_name ?? '').' '.($st->last_name ?? '')),
                'course' => trim((string) ($course->course ?? '')), 'intake' => (string) ($course->intake ?? ''), 'year' => (int) $g->year, 'count' => (int) $g->n,
                'url' => route('student.accounts', $g->student_id), 'status' => $statuses[$g->student_id] ?? null,
                'latest' => (string) $list->max('created_at'),
                'agreements' => $list->map(fn ($a) => [
                    'id' => (int) $a->id, 'date' => $a->date, 'instanceYear' => $a->instance_year, 'fees' => (float) $a->fees, 'total' => (float) $a->total,
                    'code' => $a->slc_coursecode, 'regDate' => $a->reg_date, 'linked' => (bool) $a->reg_id,
                    'by' => $a->created_by ? ($names[$a->created_by] ?? '') : '', 'at' => $when($a->created_at),
                ])->all(),
            ];
        })->sortByDesc('latest')->values()->all();
    }

    /**
     * The checks, stage, awarding body and attendance of some lines (no Identify candidates), keyed
     * by line id, with the student's full snapshot — what the actions decide on.
     */
    public function infoFor($rows): array
    {
        if ($rows->isEmpty()) {
            return [];
        }
        $out = [];
        $codeMap = self::codeMap();
        $batchById = SlcBulkBatch::all()->keyBy('id');
        foreach ($rows->groupBy('slc_bulk_import_id') as $importId => $group) {
            $import = SlcBulkImport::with('academicYear')->find($importId);
            $students = StudentSnapshots::load($group->pluck('student_id')->filter()->all(), $import?->academic_year_id);
            foreach ($group as $row) {
                $row->loadMissing('fields');
                $s = $students[$row->student_id] ?? null;
                $out[$row->id] = $this->row($row, $s, [], $codeMap, (string) ($import?->academicYear?->name ?? ''), $batchById) + ['snapshot' => $s];
            }
        }

        return $out;
    }

    /**
     * For each batch line, for the Batches › students list: what Bulk SLC Registration did
     * (status = slc_recorded, with the registration and agreement dates) or, not yet recorded, the
     * SLC registration and agreement the student already has for that academic year and year of
     * study (entered on their SLC / Accounts tab).
     */
    private function itemRecords($items): array
    {
        if ($items->isEmpty()) {
            return [];
        }
        $years = SlcBulkImport::whereIn('id', $items->pluck('slc_bulk_import_id')->unique())->pluck('academic_year_id', 'id');
        $studentIds = $items->pluck('student_id')->filter()->unique();
        $regs = DB::table('slc_registrations')->whereIn('student_id', $studentIds)->whereNull('deleted_at')->orderBy('id')
            ->get(['id', 'student_id', 'student_course_relation_id', 'academic_year_id', 'registration_year', 'confirmation_date', 'created_by']);
        $agrs = DB::table('slc_agreements')->whereIn('student_id', $studentIds)->whereNull('deleted_at')->orderBy('id')
            ->get(['id', 'student_id', 'slc_registration_id', 'year', 'date']);
        $names = self::names(array_merge($items->pluck('slc_recorded_by')->all(), $regs->pluck('created_by')->all()));
        $out = [];
        foreach ($items as $it) {
            $year = (int) ($it->data['year'] ?? 0);
            if ($it->slc_recorded) {
                $reg = $regs->firstWhere('id', $it->slc_registration_id);
                $agr = $it->slc_agreement_id ? $agrs->firstWhere('id', $it->slc_agreement_id) : null;
            } else {
                $reg = $regs->filter(fn ($g) => (int) $g->student_id === (int) $it->student_id && (int) $g->academic_year_id === (int) ($years[$it->slc_bulk_import_id] ?? 0) && (int) $g->registration_year === $year)->last();
                $agr = ($reg ? $agrs->filter(fn ($a) => (int) $a->slc_registration_id === (int) $reg->id)->last() : null)
                    ?? $agrs->filter(fn ($a) => (int) $a->student_id === (int) $it->student_id && (int) $a->year === $year)->last();
            }
            $out[$it->id] = [
                'status' => $it->slc_recorded,
                'by' => $it->slc_recorded_by ? ($names[$it->slc_recorded_by] ?? '') : '', 'at' => $this->when($it->slc_recorded_at),
                'hasReg' => (bool) $reg, 'hasAgr' => (bool) $agr,
                'regDate' => $reg?->confirmation_date, 'agrDate' => $agr?->date,
                'regBy' => $reg && $reg->created_by ? ($names[$reg->created_by] ?? '') : '',
            ];
        }

        return $out;
    }

    private function row(SlcBulkImportRow $row, ?array $s, array $pool, array $codeMap, string $yearName, $batchById): array
    {
        $r = $row->data;
        $routes = $row->fields->keyBy('field');
        $checks = [];
        $cands = [];
        $ab = null;
        $att = null;
        if ($s) {
            foreach (Checks::run($r, $s, $codeMap, $yearName) as $ch) {
                $f = $ch['ok'] ? null : $routes->get($ch['key']);
                $ch['route'] = $f?->route;
                $ch['sugg'] = $ch['ok'] ? null : Checks::suggest($ch);
                $ch['state'] = $f && $f->state ? ['state' => $f->state, 'byId' => $f->state_by, 'at' => $this->when($f->state_at)] : null;
                $ch['carried'] = (bool) $f?->carried_from_id;
                // a No action that later uploads of the same difference get by themselves (here = chosen on this line)
                $m = $ch['route'] === 'accept' ? NoActionMemory::find($this->noActions?->get($row->student_id), $r, $ch) : null;
                $ch['remembered'] = $m ? ['here' => (int) $m->slc_bulk_import_row_id === (int) $row->id, 'byId' => $m->created_by, 'at' => $this->when($m->created_at), 'file' => (string) ($m->source_file ?? '')] : null;
                $checks[] = $ch;
            }
            $ab = Checks::awardingBody($s);
            $att = Checks::attendance($s);
        } elseif (!$row->decision) {
            foreach ($pool as $p) {
                $sc = Checks::score($r, $p);
                if ($sc['pct'] >= 35) {
                    $cands[] = ['p' => $p, 'pct' => $sc['pct']];
                }
            }
            usort($cands, fn ($a, $b) => $b['pct'] <=> $a['pct']);
            // the five shown, with each chip's values for its hover card
            $cands = array_map(fn ($c) => ['s' => $this->studentOut($c['p'], $r), 'pct' => $c['pct'], 'chips' => Checks::score($r, $c['p'], true)['chips']], array_slice($cands, 0, 5));
        }

        $errors = array_values(array_filter($checks, fn ($c) => !$c['ok']));
        $unrouted = array_filter($errors, fn ($c) => !$c['route']);
        $actions = array_filter($errors, fn ($c) => in_array($c['route'], ['coc', 'contact'], true));
        $dec = null;
        if ($row->decision) {
            $batch = $batchById->get($row->slc_bulk_batch_id ?: $row->earlier_batch_id);
            $dec = [
                'd' => $row->decision, 'reason' => $row->decision_reason,
                'byId' => $row->earlier_batch_id ? null : $row->decided_by, 'by' => $row->earlier_batch_id ? 'Earlier batch' : null,
                'at' => $row->earlier_batch_id ? '' : $this->when($row->decided_at),
                'batchId' => $batch?->code, 'batchStatus' => $batch?->status,
                'carried' => $row->carried_from_id && $row->decided_at && ($this->prevDecided[$row->carried_from_id] ?? null) === $row->decided_at->toDateTimeString(),
            ];
        }
        $stage = $row->decision ? ($row->decision === 'confirm' ? 'batched' : ($row->decision === 'notreg' ? 'notreg' : 'held'))
            : (!$s ? 'identify' : ($unrouted ? 'data' : ($actions ? 'action' : 'precheck')));

        return [
            'id' => $row->id, 'line' => $row->line_no,
            'r' => array_intersect_key($r, array_flip(['title', 'first', 'other', 'last', 'gender', 'dob', 'slcCode', 'courseName', 'year', 'start', 'address', 'postcode', 'ssn', 'tfl'])),
            'link' => $row->student_id ? ['method' => $row->match_method, 'byId' => $row->linked_by] : null,
            's' => $s ? $this->studentOut($s, $r) : null,
            'checks' => $checks, 'ab' => $ab, 'att' => $att, 'cands' => $cands, 'dec' => $dec, 'stage' => $stage,
            'attChecked' => (bool) $row->attendance_checked,
            'history' => array_map(fn ($h) => $h + ['label' => $h['field'] !== '' ? (Checks::FIELD_LABEL[$h['field']] ?? $h['field']) : 'Hold', 'when' => $h['at'] ? $this->when($h['at']) : ''], $row->history ?? []),
        ];
    }

    /** What the page needs of a student (its "s"). */
    private function studentOut(array $s, array $r): array
    {
        [$addr] = Checks::bestAddress($r['address'] ?? '', $s['addresses']);

        return [
            'id' => $s['reg_no'], 'sid' => $s['id'], 'title' => $s['title'], 'first' => $s['first'], 'last' => $s['last'], 'gender' => $s['gender'],
            'dob' => $s['dob'], 'ssn' => $s['ssn'], 'address' => $addr['text'] ?? '', 'postcode' => $addr['postcode'] ?? '',
            'course' => ['name' => $s['course_name'], 'intake' => $s['intake'], 'ab' => $s['awarding_body'], 'slcCode' => (string) ($s['slc_code'] ?? ''), 'venue' => (string) ($s['venue'] ?? '')],
            'year' => $s['instance']['rank'] ?? null, 'fee' => $s['instance']['fee'] ?? null, 'url' => $s['url'],
            'uploadsUrl' => route('student.uploads', $s['id']), // the Data check's address rows link here (proof of address)
            'mobile' => $s['mobile'] ?? '', // student_contacts.mobile, shown on Actions (user, 6 Nov 2026)
            'status' => $s['status'] ?? null,
            'registration' => $s['registration'],
        ];
    }

    private function counts(array $list): array
    {
        $by = fn ($st) => count(array_filter($list, fn ($i) => $i['stage'] === $st));

        return [
            'all' => count($list), 'identify' => $by('identify'), 'data' => $by('data'), 'action' => $by('action'), 'precheck' => $by('precheck'),
            'batched' => $by('batched'), 'notreg' => $by('notreg'), 'held' => $by('held'),
            'ssn' => count(array_filter($list, fn ($i) => ($i['link']['method'] ?? null) === 'ssn')),
            'linked' => count(array_filter($list, fn ($i) => ($i['link']['method'] ?? null) === 'linked')),
            'errors' => array_sum(array_map(fn ($i) => count(array_filter($i['checks'], fn ($c) => !$c['ok'])), $list)),
        ];
    }

    /** Course mapping: the codes in this upload, mapped (via Course Creation venues) or not, and what a code can be mapped to. */
    private function courses($rows, array $codeMap, ?int $yearId): array
    {
        $codes = [];
        foreach ($rows as $row) {
            $code = strtoupper($row->data['slcCode'] ?? '');
            if ($code !== '' && !isset($codes[$code])) {
                $codes[$code] = $row->data['courseName'] ?? '';
            }
        }
        $creationIds = collect($codeMap)->flatten(1)->pluck('course_creation_id')->unique()->all();
        $instances = $creationIds ? DB::table('course_creation_instances')->whereIn('course_creation_id', $creationIds)->whereNull('deleted_at')->orderBy('start_date')->get(['course_creation_id', 'academic_year_id', 'start_date', 'fees'])->groupBy('course_creation_id') : collect();
        $mapped = [];
        $unmapped = [];
        foreach ($codes as $code => $slcName) {
            if (empty($codeMap[$code])) {
                $unmapped[] = ['code' => (string) $code, 'name' => $slcName];
                continue;
            }
            foreach ($codeMap[$code] as $e) {
                $list = $instances->get($e['course_creation_id'], collect());
                $inst = $list->firstWhere('academic_year_id', $yearId) ?? $list->first();
                $mapped[] = [
                    'code' => (string) $code, 'course' => $e['course'], 'intake' => $e['intake'], 'venue' => $e['venue'],
                    'start' => $inst ? substr((string) $inst->start_date, 0, 10) : null,
                    'fee' => (float) (($inst->fees ?? null) ?: $e['fees']), 'ab' => $e['ab'],
                ];
            }
        }
        $options = $unmapped ? DB::table('course_creation_venue as v')
            ->join('course_creations as cc', 'cc.id', '=', 'v.course_creation_id')
            ->leftJoin('courses as c', 'c.id', '=', 'cc.course_id')
            ->leftJoin('semesters as sm', 'sm.id', '=', 'cc.semester_id')
            ->leftJoin('venues as vn', 'vn.id', '=', 'v.venue_id')
            ->whereNull('v.deleted_at')->whereNull('cc.deleted_at')
            ->orderByDesc('cc.semester_id')->orderBy('c.name')
            ->get(['v.id', 'v.slc_code', 'c.name as course', 'sm.name as intake', 'vn.name as venue'])
            ->map(fn ($o) => ['id' => $o->id, 'label' => trim($o->course).' · '.$o->intake.' · '.$o->venue, 'code' => StudentSnapshots::code($o->slc_code)])->all() : [];

        return ['mapped' => $mapped, 'unmapped' => $unmapped, 'options' => $options];
    }

    /** SLC course code => the course creation venues that carry it. */
    public static function codeMap(): array
    {
        $map = [];
        $rows = DB::table('course_creation_venue as v')
            ->join('course_creations as cc', 'cc.id', '=', 'v.course_creation_id')
            ->leftJoin('courses as c', 'c.id', '=', 'cc.course_id')
            ->leftJoin('semesters as sm', 'sm.id', '=', 'cc.semester_id')
            ->leftJoin('venues as vn', 'vn.id', '=', 'v.venue_id')
            ->leftJoin('awarding_bodies as ab', 'ab.id', '=', 'c.awarding_body_id')
            ->whereNull('v.deleted_at')->whereNull('cc.deleted_at')
            ->orderByDesc('cc.id')
            ->get(['v.id', 'v.slc_code', 'v.course_creation_id', 'c.name as course', 'sm.name as intake', 'vn.name as venue', 'ab.name as ab', 'cc.fees']);
        foreach ($rows as $v) {
            $code = StudentSnapshots::code($v->slc_code);
            if ($code === '') {
                continue;
            }
            $map[$code][] = [
                'id' => (int) $v->id, 'course_creation_id' => (int) $v->course_creation_id, 'course' => trim((string) $v->course), 'intake' => trim((string) $v->intake),
                'venue' => (string) $v->venue, 'ab' => (string) $v->ab, 'fees' => (float) $v->fees,
                'label' => trim((string) $v->course).' ('.trim((string) $v->intake).')',
            ];
        }

        return $map;
    }

    /** The academic year an upload's header means: "26" is the year starting in 2026. */
    public static function academicYear(string $ay): ?AcademicYear
    {
        return ctype_digit($ay) && strlen($ay) === 2 ? AcademicYear::whereYear('from_date', 2000 + (int) $ay)->orderByDesc('id')->first() : null;
    }

    /** Staff names for user ids: "Mr First Last" from the employee record, else the user's name. */
    public static function names(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (!$userIds) {
            return [];
        }
        $out = DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id')->map(fn ($n) => (string) $n)->all();
        $emps = DB::table('employees as e')->leftJoin('titles as t', 't.id', '=', 'e.title_id')->whereIn('e.user_id', $userIds)->whereNull('e.deleted_at')->get(['e.user_id', 't.name as title', 'e.first_name', 'e.last_name']);
        foreach ($emps as $e) {
            $n = trim(trim((string) $e->first_name).' '.trim((string) $e->last_name));
            if ($n !== '') {
                $out[$e->user_id] = $n;
            }
        }

        return $out;
    }

    private function name($userId): string
    {
        return $userId ? ($this->names[(int) $userId] ?? 'User #'.$userId) : 'System';
    }

    /** Fill in the names gathered after the rows were built. */
    private function finishNames(array $info): array
    {
        foreach ($info as &$i) {
            if ($i['link']) {
                $i['link']['by'] = $this->name($i['link']['byId']);
            }
            if ($i['dec'] && $i['dec']['by'] === null) {
                $i['dec']['by'] = $this->name($i['dec']['byId']);
            }
            foreach ($i['history'] as &$h) {
                $h['byName'] = $h['by'] ? $this->name($h['by']) : '';
            }
            unset($h);
            foreach ($i['checks'] as &$c) {
                if ($c['state']) {
                    $c['state']['by'] = $this->name($c['state']['byId']);
                }
                if (!empty($c['remembered'])) {
                    $c['remembered']['by'] = $this->name($c['remembered']['byId']);
                }
            }
            unset($c);
        }
        unset($i);

        return $info;
    }

    /** Times as the page shows them: today "14:05:09", earlier "02/11 14:05". */
    private function when($t): string
    {
        if (!$t) {
            return '';
        }
        $t = $t instanceof \Carbon\Carbon ? $t : \Carbon\Carbon::parse($t);

        return $t->isToday() ? $t->format('H:i:s') : $t->format('d/m H:i');
    }
}
