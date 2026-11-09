<?php

namespace App\Http\Controllers\AttendanceIntegrity;

use App\Models\AttendanceIntegrity\AimCase;
use App\Models\AttendanceIntegrity\AimCaseActivity;
use App\Models\AttendanceIntegrity\AimCaseEvidence;
use App\Models\AttendanceIntegrity\AimFinding;
use App\Services\AttendanceIntegrity\Filters;
use App\Services\AttendanceIntegrity\IntegrityAccess;
use App\Services\AttendanceIntegrity\Lookup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Attendance Integrity > Investigations. Cases are opened from findings (or by
 * hand), worked through the statuses in the specification, and closed with an
 * outcome. Every step is written to the case's append-only history. Nothing here
 * triggers disciplinary action or student sanctions.
 *
 * View: Investigations > View. Every change: Manage investigations.
 */
class CaseController extends IntegrityBaseController
{
    public function index(Request $request)
    {
        IntegrityAccess::authorize('view_investigations');
        $f = Filters::fromRequest($request);
        IntegrityAccess::log('view', 'cases', null, $f->query());

        $q = AimCase::query()->orderByDesc('id');
        if ($f->caseStatus) {
            $q->where('investigation_status', $f->caseStatus);
        }
        if ($request->filled('subject_type')) {
            $q->where('subject_type', $request->input('subject_type'));
        }
        if ($f->staff) {
            $q->where('subject_type', 'staff')->where('subject_id', $f->staff);
        }
        if ($f->student) {
            $q->where('subject_type', 'student')->where('subject_id', $f->student);
        }
        if ($request->boolean('mine')) {
            $q->where('assigned_investigator', auth()->id());
        }
        $cases = $q->paginate(30)->withQueryString();
        $investigators = Lookup::staff($cases->pluck('assigned_investigator')->filter()->all());

        return $this->shell($request, 'pages.attendance-integrity.cases', 'Investigations', [
            'filters' => $f,
            'cases' => $cases,
            'investigators' => $investigators,
            'statusCounts' => AimCase::groupBy('investigation_status')->select('investigation_status', DB::raw('COUNT(*) AS n'))->pluck('n', 'investigation_status'),
        ]);
    }

    public function show(Request $request, AimCase $case)
    {
        IntegrityAccess::authorize('view_investigations');
        IntegrityAccess::log('case', 'case', $case->id, ['opened' => true]);
        $findings = FindingController::rows(DB::select('SELECT f.* FROM aim_findings f JOIN aim_case_findings cf ON cf.finding_id = f.id WHERE cf.case_id = ? ORDER BY f.weight DESC, f.event_at', [$case->id]));
        $people = Lookup::staff(array_merge([$case->assigned_investigator, $case->reviewed_by, $case->created_by], $case->evidenceFiles->pluck('uploaded_by')->all()));

        return $this->shell($request, 'pages.attendance-integrity.case-show', $case->case_number, [
            'case' => $case,
            'findings' => $findings,
            'activities' => $case->activities,
            'evidence' => $case->evidenceFiles,
            'people' => $people,
            'subject' => $this->subjectLink($case),
            'statuses' => AimCase::STATUSES,
            'term' => $case->term_declaration_id ? DB::table('term_declarations')->where('id', $case->term_declaration_id)->value('name') : null,
        ], false);
    }

    /** Open a case from selected findings, or for a staff member / student by hand. */
    public function store(Request $request)
    {
        IntegrityAccess::authorize('manage_investigations');
        $data = $request->validate([
            'finding_ids' => ['nullable', 'array', 'max:500'],
            'finding_ids.*' => ['integer'],
            'subject_type' => ['nullable', 'in:staff,student,class,other'],
            'subject_id' => ['nullable', 'integer'],
            'summary' => ['required', 'string', 'min:10', 'max:500'],
            'evidence' => ['nullable', 'string', 'max:5000'],
        ]);

        $findings = AimFinding::whereIn('id', $data['finding_ids'] ?? [])->get();
        $subjectType = $data['subject_type'] ?? null;
        $subjectId = $data['subject_id'] ?? null;
        if (! $subjectType && $findings->isNotEmpty()) {
            // The person most findings are about.
            $staff = $findings->pluck('staff_user_id')->filter()->countBy()->sortDesc();
            $students = $findings->pluck('student_id')->filter()->countBy()->sortDesc();
            if ($staff->isNotEmpty()) {
                [$subjectType, $subjectId] = ['staff', (int) $staff->keys()->first()];
            } elseif ($students->isNotEmpty()) {
                [$subjectType, $subjectId] = ['student', (int) $students->keys()->first()];
            }
        }
        $subjectType = $subjectType ?: 'other';
        $label = $subjectType === 'staff' ? Lookup::staffName($subjectId) : ($subjectType === 'student' ? Lookup::studentLabel($subjectId) : null);

        $case = DB::transaction(function () use ($data, $findings, $subjectType, $subjectId, $label) {
            $now = now('Europe/London');
            $year = $now->format('Y');
            $last = AimCase::where('case_number', 'like', "AIM-$year-%")->lockForUpdate()->orderByDesc('id')->value('case_number');
            $next = $last ? ((int) substr($last, -4)) + 1 : 1;

            $case = AimCase::create([
                'case_number' => sprintf('AIM-%s-%04d', $year, $next),
                'term_declaration_id' => $findings->pluck('term_declaration_id')->filter()->countBy()->sortDesc()->keys()->first(),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'subject_label' => $label,
                'risk_score' => $this->riskScore($findings),
                'summary' => $data['summary'],
                'evidence' => $data['evidence'] ?? null,
                'investigation_status' => 'New',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);
            $this->activity($case, 'created', 'Case opened: '.$data['summary'], ['findings' => $findings->count()]);
            $this->linkFindings($case, $findings);

            return $case;
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Case '.$case->case_number.' opened.', 'url' => route('attendance.integrity.cases.show', $case)]);
        }

        return redirect()->route('attendance.integrity.cases.show', $case)->with('aim_success', 'Case '.$case->case_number.' opened.');
    }

    public function status(Request $request, AimCase $case)
    {
        IntegrityAccess::authorize('manage_investigations');
        $data = $request->validate([
            'investigation_status' => ['required', 'in:'.implode(',', AimCase::STATUSES)],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);
        $resolving = str_starts_with($data['investigation_status'], 'Resolved') || $data['investigation_status'] === 'Closed';
        if ($resolving && mb_strlen(trim((string) ($data['note'] ?? ''))) < 10) {
            return back()->withErrors(['note' => 'Explain the decision (at least 10 characters) when resolving or closing a case.'])->withInput();
        }
        if ($data['investigation_status'] === $case->investigation_status) {
            return back()->with('aim_error', 'The case already has that status.');
        }

        DB::transaction(function () use ($case, $data, $resolving) {
            $old = $case->investigation_status;
            $now = now('Europe/London')->format('Y-m-d H:i:s');
            $case->forceFill(['investigation_status' => $data['investigation_status'], 'updated_by' => auth()->id(), 'updated_at' => $now]
                + ($resolving ? ['reviewed_by' => auth()->id(), 'reviewed_at' => $now] : []))->save();
            $this->activity($case, 'status', trim($old.' → '.$data['investigation_status'].(($data['note'] ?? '') !== '' ? ': '.$data['note'] : '')),
                ['from' => $old, 'to' => $data['investigation_status']]);
        });

        return back()->with('aim_success', 'Status changed to '.$data['investigation_status'].'.');
    }

    public function assign(Request $request, AimCase $case)
    {
        IntegrityAccess::authorize('manage_investigations');
        $data = $request->validate(['assigned_investigator' => ['nullable', 'integer', 'exists:users,id']]);
        $id = $data['assigned_investigator'] ?? null;
        if ($id && ! IntegrityAccess::can('view_investigations', \App\Models\User::find($id))) {
            return back()->with('aim_error', 'That person cannot view investigations, so cannot be the investigator.');
        }
        DB::transaction(function () use ($case, $id) {
            $case->forceFill(['assigned_investigator' => $id, 'updated_by' => auth()->id(), 'updated_at' => now('Europe/London')->format('Y-m-d H:i:s')])->save();
            $this->activity($case, 'assigned', $id ? 'Assigned to '.Lookup::staffName($id) : 'Investigator removed', ['user_id' => $id]);
        });

        return back()->with('aim_success', $id ? 'Investigator assigned.' : 'Investigator removed.');
    }

    public function note(Request $request, AimCase $case)
    {
        IntegrityAccess::authorize('manage_investigations');
        $data = $request->validate(['body' => ['required', 'string', 'min:3', 'max:10000'], 'kind' => ['nullable', 'in:note,explanation']]);
        DB::transaction(function () use ($case, $data) {
            $this->activity($case, ($data['kind'] ?? 'note') === 'explanation' ? 'explanation' : 'note', $data['body']);
            $case->forceFill(['updated_by' => auth()->id(), 'updated_at' => now('Europe/London')->format('Y-m-d H:i:s')])->save();
        });

        return back()->with('aim_success', 'Added to the case history.');
    }

    public function outcome(Request $request, AimCase $case)
    {
        IntegrityAccess::authorize('manage_investigations');
        $data = $request->validate(['outcome' => ['required', 'string', 'min:10', 'max:10000']]);
        DB::transaction(function () use ($case, $data) {
            $now = now('Europe/London')->format('Y-m-d H:i:s');
            $case->forceFill(['outcome' => $data['outcome'], 'reviewed_by' => auth()->id(), 'reviewed_at' => $now, 'updated_by' => auth()->id(), 'updated_at' => $now])->save();
            $this->activity($case, 'outcome', $data['outcome']);
        });

        return back()->with('aim_success', 'Outcome recorded.');
    }

    public function evidence(Request $request, AimCase $case)
    {
        IntegrityAccess::authorize('manage_investigations');
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,txt,msg,eml'],
        ]);

        DB::transaction(function () use ($case, $data, $request) {
            $file = $request->file('file');
            $path = null;
            if ($file) {
                $path = $file->storeAs('aim-evidence/'.$case->id, Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), 'local');
            }
            $evidence = AimCaseEvidence::create([
                'case_id' => $case->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'file_path' => $path,
                'file_name' => $file ? mb_substr($file->getClientOriginalName(), 0, 255) : null,
                'mime' => $file ? mb_substr((string) $file->getMimeType(), 0, 120) : null,
                'size' => $file ? $file->getSize() : null,
                'sha256' => $file ? hash_file('sha256', Storage::disk('local')->path($path)) : null,
                'uploaded_by' => auth()->id(),
                'created_at' => now('Europe/London')->format('Y-m-d H:i:s'),
            ]);
            $this->activity($case, 'evidence', 'Evidence added: '.$data['title'].($file ? ' ('.$evidence->file_name.')' : ''), ['evidence_id' => $evidence->id]);
            $case->forceFill(['updated_by' => auth()->id(), 'updated_at' => now('Europe/London')->format('Y-m-d H:i:s')])->save();
        });

        return back()->with('aim_success', 'Evidence added.');
    }

    public function download(AimCase $case, AimCaseEvidence $evidence)
    {
        IntegrityAccess::authorize('view_investigations');
        abort_unless((int) $evidence->case_id === (int) $case->id && $evidence->file_path && Storage::disk('local')->exists($evidence->file_path), 404);
        IntegrityAccess::log('export', 'case_evidence', $evidence->id, ['case' => $case->case_number]);

        return Storage::disk('local')->download($evidence->file_path, $evidence->file_name ?: basename($evidence->file_path));
    }

    /** Add more findings to an open case. */
    public function addFindings(Request $request, AimCase $case)
    {
        IntegrityAccess::authorize('manage_investigations');
        $data = $request->validate(['finding_ids' => ['required', 'array', 'max:500'], 'finding_ids.*' => ['integer']]);
        $findings = AimFinding::whereIn('id', $data['finding_ids'])->get();
        DB::transaction(function () use ($case, $findings) {
            $added = $this->linkFindings($case, $findings);
            $all = AimFinding::whereIn('id', DB::table('aim_case_findings')->where('case_id', $case->id)->pluck('finding_id'))->get();
            $case->forceFill(['risk_score' => $this->riskScore($all), 'updated_by' => auth()->id(), 'updated_at' => now('Europe/London')->format('Y-m-d H:i:s')])->save();
            $this->activity($case, 'findings', $added.' '.($added === 1 ? 'finding' : 'findings').' added', ['finding_ids' => $findings->pluck('id')->all()]);
        });

        return response()->json(['message' => $findings->count().' findings added to '.$case->case_number.'.', 'url' => route('attendance.integrity.cases.show', $case)]);
    }

    /** Open cases (for "add to an existing case"). */
    public function openCases()
    {
        IntegrityAccess::authorize('manage_investigations');

        return response()->json(AimCase::whereIn('investigation_status', AimCase::OPEN_STATUSES)->orderByDesc('id')->limit(100)
            ->get(['id', 'case_number', 'subject_label', 'summary'])->map(fn ($c) => ['id' => $c->id, 'name' => $c->case_number.' · '.($c->subject_label ?: '').' · '.mb_substr($c->summary, 0, 60)]));
    }

    private function linkFindings(AimCase $case, $findings): int
    {
        $added = 0;
        $now = now('Europe/London')->format('Y-m-d H:i:s');
        foreach ($findings as $finding) {
            $inserted = DB::table('aim_case_findings')->insertOrIgnore(['case_id' => $case->id, 'finding_id' => $finding->id, 'added_by' => auth()->id(), 'created_at' => $now]);
            $added += $inserted;
            if (! $finding->case_id) {
                $finding->forceFill(['status' => 'case', 'case_id' => $case->id, 'updated_at' => $now])->save();
            }
        }

        return $added;
    }

    /** The case's score: the same policy as everywhere (highest points per event). */
    private function riskScore($findings): int
    {
        return (int) $findings->groupBy('event_key')->map(fn ($g) => $g->max('weight'))->sum();
    }

    private function activity(AimCase $case, string $type, string $body, array $meta = []): void
    {
        AimCaseActivity::create([
            'case_id' => $case->id,
            'type' => $type,
            'body' => $body,
            'meta' => $meta ?: null,
            'created_by' => auth()->id(),
            'created_by_name' => Lookup::staffName(auth()->id()),
            'created_at' => now('Europe/London')->format('Y-m-d H:i:s'),
        ]);
    }

    private function subjectLink(AimCase $case): array
    {
        if ($case->subject_type === 'staff' && $case->subject_id) {
            return ['label' => $case->subject_label ?: Lookup::staffName($case->subject_id), 'url' => IntegrityAccess::can('view_staff') ? route('attendance.integrity.staff.show', $case->subject_id) : null];
        }
        if ($case->subject_type === 'student' && $case->subject_id) {
            return ['label' => $case->subject_label ?: Lookup::studentLabel($case->subject_id), 'url' => IntegrityAccess::can('view_students') ? route('attendance.integrity.students.show', $case->subject_id) : null];
        }

        return ['label' => $case->subject_label ?: (AimCase::SUBJECT_TYPES[$case->subject_type] ?? 'Other'), 'url' => null];
    }
}
