<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskAssignmentRuleRequest;
use App\Models\ProcessList;
use App\Models\TaskAssignmentRule;
use App\Models\TaskAssignmentRuleCondition;
use App\Models\Applicant;
use App\Models\ApplicantTask;
use App\Models\TaskAssignmentRun;
use App\Models\TaskList;
use App\Models\Student;
use App\Models\StudentTask;
use App\Services\TaskAssignment\TaskAssigner;
use App\Services\TaskAssignment\ApplicationFacts;
use App\Services\TaskAssignment\StudentFacts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Site settings > Applicant Settings > Task Automation.
 *
 * The rules that assign tasks automatically, in two halves picked with
 * ?subject=: applicant rules (Applicant-phase tasks, on "Application
 * submitted") and student rules (Live-phase tasks, on "Student created" /
 * "Student status changed"). This screen only edits them; the engine
 * (TaskAssigner) applies them and reads the same tables.
 */
class TaskAssignmentRuleController extends Controller
{
    public function index(Request $request)
    {
        $subject = $this->subject($request);
        $phase = $subject === TaskAssignmentRule::SUBJECT_STUDENT ? 'Live' : 'Applicant';
        $processes = ProcessList::where('phase', $phase)->orderBy('name')->get(['id', 'name', 'auto_feed']);
        $processIds = $processes->pluck('id');
        $facts = StudentFacts::forSubject($subject);

        return view('pages.settings.taskrules.index', [
            'title' => 'Task Automation - London Churchill College',
            'subtitle' => 'Applicant Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'Task Automation', 'href' => 'javascript:void(0);'],
            ],
            'subject' => $subject,
            'subjectCounts' => [
                TaskAssignmentRule::SUBJECT_APPLICANT => TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_APPLICANT)->count(),
                TaskAssignmentRule::SUBJECT_STUDENT => TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_STUDENT)->count(),
            ],
            // The dialog picks a process, then one of its tasks (the rule stores the task; its process follows from it).
            'processes' => $processes,
            'tasks' => TaskList::whereIn('process_list_id', $processIds)->orderBy('name')->get(['id', 'name', 'process_list_id']),
            'triggers' => TaskAssignmentRule::triggersFor($subject),
            'catalogue' => $facts::catalogue(),
            'groups' => TaskAssignmentRule::forSubject($subject)->whereNotNull('exclusive_group')->distinct()->orderBy('exclusive_group')->pluck('exclusive_group'),
            'mode' => $this->mode($subject),
            'jobs' => $this->jobs($subject),
        ]);
    }

    /** applicant (default) or student, from ?subject= */
    private function subject(Request $request): string
    {
        return $request->input('subject') === TaskAssignmentRule::SUBJECT_STUDENT ? TaskAssignmentRule::SUBJECT_STUDENT : TaskAssignmentRule::SUBJECT_APPLICANT;
    }

    private function mode(string $subject): string
    {
        return $subject === TaskAssignmentRule::SUBJECT_STUDENT ? config('tasks.student_mode', 'live') : config('tasks.mode', 'live');
    }

    private function jobs(string $subject): array
    {
        // return (array) config($subject === TaskAssignmentRule::SUBJECT_STUDENT ? 'tasks.student_assignment_jobs' : 'tasks.assignment_jobs', []);
        if ($subject === TaskAssignmentRule::SUBJECT_STUDENT) {
            return (array) config('tasks.student_assignment_jobs', []);
        }

        // applicant: actions run on assignment, plus those run when the task is completed
        return (array) config('tasks.assignment_jobs', []) + (array) config('tasks.completion_jobs', []);
    }

    public function list(Request $request)
    {
        $status = (int) ($request->status ?: 1);
        $queryStr = trim((string) $request->querystr);

        $subject = $this->subject($request);
        $query = TaskAssignmentRule::with(['task.processlist', 'conditions'])->forSubject($subject)
            ->orderBy('exclusive_group')->orderBy('priority')->orderBy('id');
        if ($status === 2) {
            $query->onlyTrashed();
        }
        if ($queryStr !== '') {
            $query->where(function ($q) use ($queryStr) {
                $q->whereHas('task', fn($t) => $t->where('name', 'LIKE', "%$queryStr%"))
                  ->orWhere('exclusive_group', 'LIKE', "%$queryStr%");
            });
        }

        $catalogue = StudentFacts::forSubject($subject)::catalogue();
        $jobs = $this->jobs($subject);
        $data = [];
        foreach ($query->get() as $rule) {
            $data[] = [
                'id' => $rule->id,
                'task' => optional($rule->task)->name,
                'task_list_id' => $rule->task_list_id,
                'process' => optional(optional($rule->task)->processlist)->name,
                'trigger' => TaskAssignmentRule::TRIGGERS[$rule->trigger] ?? $rule->trigger,
                'exclusive_group' => $rule->exclusive_group,
                'priority' => $rule->priority,
                'active' => $rule->active,
                'conditions' => $rule->conditions->map(fn($c) => $this->describe($c, $catalogue))->values(),
                'job' => $rule->after_assign_job ? ($jobs[$rule->after_assign_job] ?? class_basename($rule->after_assign_job)) : null,
                'auto_complete' => $subject === TaskAssignmentRule::SUBJECT_STUDENT ? $this->completesOnConversion($rule) : null,
                'deleted_at' => $rule->deleted_at,
            ];
        }

        return response()->json(['last_page' => 1, 'data' => $data]);
    }

    /**
     * The Student list's "Auto Complete" column: the rule runs at the conversion ("Student created")
     * and its action completes the task there (config tasks.conversion_auto_complete_jobs).
     */
    private function completesOnConversion(TaskAssignmentRule $rule): bool
    {
        return $rule->trigger === TaskAssigner::TRIGGER_STUDENT_CREATED
            && in_array($rule->after_assign_job, (array) config('tasks.conversion_auto_complete_jobs', []), true);
    }

    private function describe(TaskAssignmentRuleCondition $c, array $catalogue): string
    {
        return ApplicationFacts::describe($c->field, $c->operator, $c->value, $catalogue);
    }

    /**
     * Dry run for one applicant (or, with subject=student, one student): the facts,
     * every rule's verdict and the tasks that would be created. Nothing is written,
     * not even a run row.
     */
    public function preview(Request $request, TaskAssigner $assigner)
    {
        if ($this->subject($request) === TaskAssignmentRule::SUBJECT_STUDENT) {
            return $this->previewStudent($request, $assigner);
        }

        $ref = trim((string) $request->applicant);
        $applicant = ctype_digit($ref) ? Applicant::find((int) $ref) : null;
        if (!$applicant && $ref !== '') {
            $applicant = Applicant::where('application_no', $ref)->first();
        }
        if (!$applicant) {
            return response()->json(['message' => 'No applicant with that id or reference.'], 404);
        }

        $plan = $assigner->plan($applicant, $request->input('trigger', TaskAssigner::TRIGGER_SUBMITTED));
        $existing = ApplicantTask::with('task')->where('applicant_id', $applicant->id)->get()
            ->map(fn($t) => ['task' => optional($t->task)->name, 'status' => $t->status, 'via' => $t->assigned_via ?: 'manual'])->values();
        $names = TaskList::whereIn('id', array_keys($plan['assign']))->pluck('name', 'id');

        return response()->json([
            'applicant' => [
                'id' => $applicant->id,
                'name' => trim($applicant->first_name . ' ' . $applicant->last_name),
                'application_no' => $applicant->application_no,
                'status_id' => $applicant->status_id,
                'submitted' => $applicant->submission_date,
            ],
            'facts' => $plan['facts'],
            'labels' => collect(ApplicationFacts::fields())->map(fn($f) => $f['label']),
            'evaluations' => $plan['evaluations'],
            'would_assign' => collect($plan['assign'])->map(fn($ruleId, $taskId) => ['task_list_id' => $taskId, 'task' => $names[$taskId] ?? $taskId, 'rule_id' => $ruleId])->values(),
            'existing' => $existing,
            'mode' => config('tasks.mode', 'live'),
        ]);
    }

    /** The student preview: by id or registration no., for "Student created" or "Student status changed". */
    private function previewStudent(Request $request, TaskAssigner $assigner)
    {
        $ref = trim((string) $request->student);
        $student = ctype_digit($ref) ? Student::find((int) $ref) : null;
        if (!$student && $ref !== '') {
            $student = Student::where('registration_no', $ref)->first() ?? Student::where('registration_no', 'LCC' . $ref)->first();
        }
        if (!$student) {
            return response()->json(['message' => 'No student with that id or registration no.'], 404);
        }

        $trigger = array_key_exists((string) $request->trigger, TaskAssignmentRule::triggersFor(TaskAssignmentRule::SUBJECT_STUDENT)) ? $request->trigger : TaskAssigner::TRIGGER_STUDENT_CREATED;
        $context = $trigger === TaskAssigner::TRIGGER_STUDENT_STATUS_CHANGED && $request->filled('previous_status_id') ? ['previous_status_id' => (int) $request->previous_status_id] : [];
        $plan = $assigner->planStudent($student, $trigger, $context);
        $existing = StudentTask::with('task')->where('student_id', $student->id)->get()
            ->map(fn($t) => ['task' => optional($t->task)->name, 'status' => $t->status, 'via' => $t->assigned_via ?: ($t->applicant_task_id ? 'from application' : 'manual')])->values();
        $names = TaskList::whereIn('id', array_keys($plan['assign']))->pluck('name', 'id');
        $student->loadMissing('status');

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')),
                'registration_no' => $student->registration_no,
                'status' => optional($student->status)->name,
                'status_id' => $student->status_id,
            ],
            'trigger' => $trigger,
            'facts' => $plan['facts'],
            'labels' => collect(StudentFacts::fields())->map(fn($f) => $f['label']),
            'evaluations' => $plan['evaluations'],
            'would_assign' => collect($plan['assign'])->map(fn($ruleId, $taskId) => ['task_list_id' => $taskId, 'task' => $names[$taskId] ?? $taskId, 'rule_id' => $ruleId])->values(),
            'existing' => $existing,
            'mode' => config('tasks.student_mode', 'live'),
        ]);
    }

    /** The last runs the engine made, newest first. */
    public function runs(Request $request)
    {
        $subject = $this->subject($request);
        $isStudent = $subject === TaskAssignmentRule::SUBJECT_STUDENT;
        $runs = TaskAssignmentRun::with($isStudent ? 'student' : 'applicant')
            ->when($isStudent, fn($q) => $q->whereNotNull('student_id'), fn($q) => $q->whereNull('student_id'))
            ->orderByDesc('id')->limit((int) ($request->limit ?: 20))->get();
        $data = $runs->map(function ($r) use ($isStudent) {
            $ev = collect($r->evaluations ?? []);
            $who = $isStudent ? $r->student : $r->applicant;
            return [
                'id' => $r->id,
                'applicant_id' => $isStudent ? $r->student_id : $r->applicant_id,
                'applicant' => $who ? trim($who->first_name . ' ' . $who->last_name) : '—',
                'application_no' => $isStudent ? optional($who)->registration_no : optional($who)->application_no,
                'mode' => $r->mode,
                'trigger' => TaskAssignmentRule::TRIGGERS[$r->trigger] ?? $r->trigger,
                'rules_matched' => $ev->where('matched', true)->count(),
                'rules_total' => $ev->count(),
                'tasks_created' => $r->tasks_created,
                'assigned' => $ev->where('outcome', TaskAssigner::ASSIGN)->pluck('task')->values(),
                'when' => $r->created_at ? $r->created_at->format('d M Y H:i') : '',
            ];
        })->values();

        return response()->json(['data' => $data, 'mode' => $this->mode($subject)]);
    }

    public function store(TaskAssignmentRuleRequest $request)
    {
        $rule = DB::transaction(function () use ($request) {
            $rule = TaskAssignmentRule::create($this->attributes($request) + ['created_by' => auth()->id()]);
            $this->syncConditions($rule, $request->input('conditions', []));
            return $rule;
        });

        return response()->json(['message' => 'Rule saved.', 'id' => $rule->id], 200);
    }

    public function edit($id)
    {
        $rule = TaskAssignmentRule::with('conditions')->withTrashed()->find($id);
        if (!$rule) {
            return response()->json(['message' => 'Rule not found.'], 404);
        }
        return response()->json([
            'id' => $rule->id,
            'task_list_id' => $rule->task_list_id,
            'trigger' => $rule->trigger,
            'exclusive_group' => $rule->exclusive_group,
            'priority' => $rule->priority,
            'active' => $rule->active,
            'notes' => $rule->notes,
            'after_assign_job' => $rule->after_assign_job,
            'conditions' => $rule->conditions->map(fn($c) => ['field' => $c->field, 'operator' => $c->operator, 'value' => $c->value])->values(),
        ]);
    }

    public function update(TaskAssignmentRuleRequest $request)
    {
        $rule = TaskAssignmentRule::find($request->id);
        if (!$rule) {
            return response()->json(['message' => 'Rule not found.'], 404);
        }
        DB::transaction(function () use ($rule, $request) {
            $rule->update($this->attributes($request) + ['updated_by' => auth()->id()]);
            $this->syncConditions($rule, $request->input('conditions', []));
        });

        return response()->json(['message' => 'Rule updated.'], 200);
    }

    public function destroy($id)
    {
        $rule = TaskAssignmentRule::find($id);
        if ($rule) {
            $rule->delete();
        }
        return response()->json(['message' => 'Rule archived.'], 200);
    }

    public function restore($id)
    {
        TaskAssignmentRule::withTrashed()->where('id', $id)->restore();
        return response()->json(['message' => 'Rule restored.'], 200);
    }

    private function attributes(TaskAssignmentRuleRequest $request): array
    {
        return [
            'task_list_id' => $request->task_list_id,
            'trigger' => $request->trigger,
            'exclusive_group' => trim((string) $request->exclusive_group) !== '' ? trim($request->exclusive_group) : null,
            'priority' => (int) $request->priority,
            'active' => $request->boolean('active'),
            'notes' => $request->notes,
            'after_assign_job' => $request->filled('after_assign_job') ? $request->after_assign_job : null,
        ];
    }

    /* Conditions are replaced wholesale: the form always posts the full set. */
    private function syncConditions(TaskAssignmentRule $rule, array $conditions): void
    {
        $rule->conditions()->delete();
        foreach (array_values($conditions) as $i => $c) {
            $isList = in_array($c['operator'], ['in', 'not_in'], true);
            $value = $isList
                ? array_values(array_filter((array) ($c['value'] ?? []), fn($x) => $x !== '' && $x !== null))
                : ($c['value'] ?? null);
            $rule->conditions()->create([
                'field' => $c['field'],
                'operator' => $c['operator'],
                'value' => $value,
                'sort_order' => $i,
            ]);
        }
    }
}
