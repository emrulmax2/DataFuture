<?php

namespace App\Http\Controllers\HR\PolicyAssessment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PolicyAssessment\PolicyDocumentRequest;
use App\Models\PolicyAssignment;
use App\Models\PolicyCategory;
use App\Models\PolicyDocument;
use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Policy Assessments › Question Bank. One row per policy with its test rules
 * (pass mark, questions per test, attempts, time limit) and how ready its
 * question bank is: active questions and drafts to review, overall and per
 * level, and whether each exam can run. An exam never draws from one level
 * alone — it takes a set share of Beginner, Intermediate and Expert
 * questions (PolicyLevel::MIX) — so an exam is ready only when every level
 * has enough active questions for its share.
 */
class PolicyDocumentController extends Controller
{
    /** How many questions HR aims to have in each policy's bank. */
    const BANK_TARGET = 50;

    /** The "Questions" filter values. */
    const REVIEW_FILTERS = ['drafts', 'none', 'exam_gap', 'below_target'];

    /** Client sort field => SQL column / withCount alias. Anything else is ignored. */
    const SORTABLE = [
        'id' => 'id',
        'title' => 'title',
        'sort_order' => 'sort_order',
        'pass_mark' => 'pass_mark',
        'questions_per_attempt' => 'questions_per_attempt',
        'rules_summary' => 'questions_per_attempt',
        'max_attempts' => 'max_attempts',
        'time_limit_minutes' => 'time_limit_minutes',
        'is_active' => 'is_active',
        'questions' => 'active_questions_count',
        'assigned' => 'assignments_count',
    ];

    public function index()
    {
        $this->guard();

        return view('pages.hr.policy-assessment.policies', [
            'title' => 'Policy Question Bank - London Churchill College',
            'subtitle' => 'Question Bank',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Policy Assessments', 'href' => route('policy.assessment')],
                ['label' => 'Question Bank', 'href' => 'javascript:void(0);'],
            ],
            'categories' => PolicyCategory::orderBy('sort_order', 'ASC')->orderBy('name', 'ASC')->get(),
            'bankTarget' => self::BANK_TARGET,
            'examMix' => self::examPattern(0)['exams'],
        ]);
    }

    public function list(Request $request)
    {
        $this->guard();

        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? trim($request->querystr) : '');
        $status = (isset($request->status) && in_array((string) $request->status, ['0', '1', '2', '3'], true) ? (int) $request->status : 3);
        $category = (isset($request->category) && $request->category > 0 ? (int) $request->category : 0);
        $review = (isset($request->review) && in_array($request->review, self::REVIEW_FILTERS, true) ? $request->review : '');

        $query = PolicyDocument::query();
        if($queryStr !== ''):
            $query->where(function ($q) use ($queryStr) {
                $q->where('title', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('version', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('slug', 'LIKE', '%'.$queryStr.'%');
            });
        endif;
        if($category > 0):
            $query->where('policy_category_id', $category);
        endif;
        if($status == 2):
            $query->onlyTrashed();
        elseif($status == 1 || $status == 0):
            $query->where('is_active', $status);
        endif;
        if($review == 'drafts'):
            $query->whereHas('questions', function ($q) {
                $q->where('is_active', 0);
            });
        elseif($review == 'none'):
            $query->whereDoesntHave('questions', function ($q) {
                $q->where('is_active', 1);
            });
        elseif($review == 'exam_gap'):
            /* At least one exam that cannot be drawn in full. */
            $query->whereIn('id', $this->examGapIds($query));
        elseif($review == 'below_target'):
            $query->has('questions', '<', self::BANK_TARGET);
        endif;

        $total_rows = (clone $query)->count();
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? (int) $request->size : 10));
        $perpage = max(1, $perpage);
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query->with(['category' => function ($q) {
            $q->withTrashed();
        }]);
        $counts = [
            'questions',
            'questions as active_questions_count' => function ($q) {
                $q->where('is_active', 1);
            },
            'questions as draft_questions_count' => function ($q) {
                $q->where('is_active', 0);
            },
            'assignments',
            'assignments as passed_assignments_count' => function ($q) {
                $q->where('status', PolicyAssignment::STATUS_PASSED);
            },
        ];
        foreach(PolicyLevel::all() as $level):
            $counts['questions as '.$level.'_questions_count'] = function ($q) use ($level) {
                $q->ofLevel($level);
            };
            $counts['questions as '.$level.'_active_questions_count'] = function ($q) use ($level) {
                $q->ofLevel($level)->where('is_active', 1);
            };
        endforeach;
        $query->withCount($counts);
        /* drawable_questions_count + drawable_<level>_count, so no query per row. */
        $query->withDrawableCounts();
        $this->applySorters($query, $request);

        $Query = $query->skip($offset)->take($perpage)->get();

        $data = [];
        if(!empty($Query)):
            $i = $offset + 1;
            foreach($Query as $list):
                $levels = [];
                $levelDrafts = [];
                foreach(PolicyLevel::all() as $level):
                    $levelActive = (int) $list->getAttribute($level.'_active_questions_count');
                    $levelTotal = (int) $list->getAttribute($level.'_questions_count');
                    $levels[$level] = [
                        'level' => $level,
                        'label' => PolicyLevel::label($level),
                        'active' => $levelActive,
                        'total' => $levelTotal,
                        'drawable' => $list->drawableQuestionCount($level),
                    ];
                    $levelDrafts[$level] = max(0, $levelTotal - $levelActive);
                endforeach;

                /* Per exam: ready or not, and what each level is short by. */
                $exams = self::examReadiness($list, $levelDrafts);
                $examsReady = 0;
                foreach($exams as $exam):
                    $examsReady += ($exam['ready'] ? 1 : 0);
                endforeach;
                $timeLimit = ($list->isTimed() ? (int) $list->time_limit_minutes : null);

                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'title' => $list->title,
                    'version' => $list->version,
                    'category' => (isset($list->category->name) ? $list->category->name : ''),
                    'category_archived' => (isset($list->category) && $list->category->trashed() ? 1 : 0),
                    'pdf_url' => $list->pdf_url,
                    'page_url' => $list->page_url,
                    'pass_mark' => (int) $list->pass_mark,
                    'questions_per_attempt' => (int) $list->questions_per_attempt,
                    'max_attempts' => ($list->max_attempts !== null ? (int) $list->max_attempts : null),
                    'time_limit_minutes' => $timeLimit,
                    'time_limit_label' => self::timeLimitLabel($timeLimit),
                    'rules_summary' => self::rulesSummary($list),
                    'sort_order' => (int) $list->sort_order,
                    'is_active' => ($list->is_active ? 1 : 0),
                    'questions' => (int) $list->active_questions_count,
                    'questions_total' => (int) $list->questions_count,
                    'drafts' => (int) $list->draft_questions_count,
                    'drawable' => $list->drawableQuestionCount(),
                    'levels' => $levels,
                    'exams' => $exams,
                    'exams_ready' => $examsReady,
                    'exam_gap' => ($examsReady < count($exams) ? 1 : 0),
                    'bank_target' => self::BANK_TARGET,
                    'below_target' => ((int) $list->questions_count < self::BANK_TARGET ? 1 : 0),
                    'assigned' => (int) $list->assignments_count,
                    'passed' => (int) $list->passed_assignments_count,
                    'questions_url' => (!$list->trashed() ? route('policy.assessment.question', $list->id) : null),
                    'deleted_at' => $list->deleted_at,
                ];
                $i++;
            endforeach;
        endif;

        return response()->json(['last_page' => $last_page, 'total_rows' => $total_rows, 'data' => $data]);
    }

    public function store(PolicyDocumentRequest $request)
    {
        $this->guard();

        $values = $this->values($request);
        $values['slug'] = Str::limit(Str::slug($values['title']), 191, '');
        if(!isset($request->sort_order) || $request->sort_order === ''):
            $values['sort_order'] = (int) PolicyDocument::withTrashed()->where('policy_category_id', $values['policy_category_id'])->max('sort_order') + 1;
        endif;
        $values['created_by'] = auth()->user()->id;

        $policy = PolicyDocument::create($values);

        return response()->json(['message' => 'Policy created.', 'id' => $policy->id, 'questions_url' => route('policy.assessment.question', $policy->id)], 200);
    }

    public function edit(PolicyDocument $policy)
    {
        $this->guard();

        /* The category select only lists live categories, so an archived one
           is sent by name for the modal to show as the current choice. */
        $category = PolicyCategory::withTrashed()->find($policy->policy_category_id);

        return response()->json([
            'id' => $policy->id,
            'policy_category_id' => $policy->policy_category_id,
            'category_name' => (isset($category->name) ? $category->name : ''),
            'category_archived' => (isset($category) && $category->trashed()),
            'title' => $policy->title,
            'version' => $policy->version,
            'pdf_url' => $policy->pdf_url,
            'page_url' => $policy->page_url,
            'description' => $policy->description,
            'pass_mark' => (int) $policy->pass_mark,
            'questions_per_attempt' => (int) $policy->questions_per_attempt,
            'max_attempts' => ($policy->max_attempts !== null ? (int) $policy->max_attempts : null),
            'time_limit_minutes' => ($policy->isTimed() ? (int) $policy->time_limit_minutes : null),
            'sort_order' => (int) $policy->sort_order,
            'is_active' => ($policy->is_active ? 1 : 0),
        ]);
    }

    public function update(PolicyDocumentRequest $request, PolicyDocument $policy)
    {
        $this->guard();

        $policy->fill($this->values($request));
        if(!isset($request->sort_order) || $request->sort_order === ''):
            $policy->sort_order = (int) $policy->getOriginal('sort_order');
        endif;

        if(!$policy->isDirty()):
            return response()->json(['message' => 'No data Modified'], 304);
        endif;

        $policy->updated_by = auth()->user()->id;
        $policy->save();

        return response()->json(['message' => 'Data updated'], 200);
    }

    /**
     * Switch a policy on or off for testing. Pass is_active=0|1 to set it
     * explicitly (a double click cannot flip it twice); without it, it flips.
     */
    public function toggleStatus(Request $request, PolicyDocument $policy)
    {
        $this->guard();

        $target = ($request->has('is_active') ? ($request->input('is_active') == 1) : !$policy->is_active);
        if($policy->is_active != $target):
            $policy->is_active = $target;
            $policy->updated_by = auth()->user()->id;
            $policy->save();
        endif;

        return response()->json([
            'is_active' => ($policy->is_active ? 1 : 0),
            'message' => ($policy->is_active ? 'Policy is active.' : 'Policy is inactive. Staff cannot start its test.'),
        ], 200);
    }

    public function destroy($id)
    {
        $this->guard();

        $policy = PolicyDocument::findOrFail($id);
        $policy->updated_by = auth()->user()->id;
        $policy->save();
        $policy->delete();

        return response()->json(['message' => 'Policy archived.'], 200);
    }

    public function restore($id)
    {
        $this->guard();

        $policy = PolicyDocument::onlyTrashed()->findOrFail($id);
        $policy->restore();

        return response()->json(['message' => 'Policy restored.'], 200);
    }

    private function guard(): void
    {
        abort_unless(PolicyAssessmentService::canManage(), 403, 'You are not permitted to manage policy assessments.');
    }

    /**
     * How ready each exam is for one policy, worded once here so the Question
     * Bank list and the questions page (Blade and JS) say the same thing:
     * [exam level => [level, label, name, ready, state (ready|blocked),
     * state_label, needed, available, short (each [question level => n]),
     * short_total, needs_text, action, note, summary]].
     *
     * An exam is ready when every level can fill its share of the questions
     * per test; a test is never topped up from another level. Load $policy
     * with withDrawableCounts() and nothing is queried here. $drafts
     * ([question level => drafts waiting]) picks the verb: "activate" when
     * the drafts cover the shortfall, "add" when there are none.
     */
    public static function examReadiness(PolicyDocument $policy, array $drafts = []): array
    {
        $exams = [];
        foreach(PolicyLevel::all() as $exam):
            $needed = [];
            $available = [];
            $short = [];
            foreach($policy->examRequirements($exam) as $level => $row):
                $needed[$level] = (int) $row['needed'];
                $available[$level] = (int) $row['available'];
                $short[$level] = (int) $row['short'];
            endforeach;

            $ready = $policy->examReady($exam);
            $name = PolicyLevel::label($exam).' exam';

            $needsParts = [];
            foreach($needed as $level => $count):
                $needsParts[] = $count.' '.PolicyLevel::label($level);
            endforeach;
            $needsText = implode(' · ', $needsParts);

            /* "activate 1 more Intermediate and 5 more Expert" */
            $actionParts = [];
            $lastVerb = '';
            foreach($short as $level => $count):
                if($count <= 0):
                    continue;
                endif;
                $waiting = (isset($drafts[$level]) ? (int) $drafts[$level] : $count);
                $verb = ($waiting >= $count ? 'activate' : ($waiting <= 0 ? 'add' : 'add or activate'));
                $actionParts[] = ($verb !== $lastVerb ? $verb.' ' : '').$count.' more '.PolicyLevel::label($level);
                $lastVerb = $verb;
            endforeach;
            $action = self::joinList($actionParts);

            $exams[$exam] = [
                'level' => $exam,
                'label' => PolicyLevel::label($exam),
                'name' => $name,
                'ready' => $ready,
                'state' => ($ready ? 'ready' : 'blocked'),
                'state_label' => ($ready ? 'Ready' : 'Not ready'),
                'needed' => $needed,
                'available' => $available,
                'short' => $short,
                'short_total' => array_sum($short),
                'needs_text' => $needsText,
                'action' => $action,
                'note' => ($ready
                    ? 'Every level has enough active questions.'
                    : ucfirst($action).' '.(array_sum($short) == 1 ? 'question' : 'questions').'.'),
                'summary' => ($ready
                    ? $name.' — needs '.$needsText.' · Ready'
                    : $name.' — needs '.implode(' / ', $needed).' · has '.implode(' / ', $available).' · '.$action),
            ];
        endforeach;

        return $exams;
    }

    /**
     * The question pattern of each exam for a test of $perTest questions:
     * ['per_test' => n, 'exams' => [exam level => [level, label, name,
     * mix ([question level => %]), quota ([question level => questions])]]].
     * The percentages are PolicyLevel::MIX — pages take them from here and
     * never spell them out themselves.
     */
    public static function examPattern(int $perTest): array
    {
        $perTest = max(0, $perTest);
        $exams = [];
        foreach(PolicyLevel::all() as $exam):
            $exams[$exam] = [
                'level' => $exam,
                'label' => PolicyLevel::label($exam),
                'name' => PolicyLevel::label($exam).' exam',
                'mix' => PolicyLevel::mix($exam),
                'quota' => PolicyLevel::quota($exam, $perTest),
            ];
        endforeach;

        return ['per_test' => $perTest, 'exams' => $exams];
    }

    /** "15 min", "1 hr", "1 hr 30 min"; no limit reads "No time limit". */
    public static function timeLimitLabel(?int $minutes): string
    {
        if($minutes === null || $minutes <= 0):
            return 'No time limit';
        endif;
        if($minutes < 60):
            return $minutes.' min';
        endif;

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $hours.' hr'.($rest > 0 ? ' '.$rest.' min' : '');
    }

    /** The test rules on one line: "10 questions · 80% to pass · 15 min". */
    public static function rulesSummary(PolicyDocument $policy): string
    {
        $perTest = (int) $policy->questions_per_attempt;

        return $perTest.' '.($perTest == 1 ? 'question' : 'questions')
            .' · '.(int) $policy->pass_mark.'% to pass'
            .' · '.self::timeLimitLabel($policy->isTimed() ? (int) $policy->time_limit_minutes : null);
    }

    /** "a", "a and b", "a, b and c". */
    public static function joinList(array $parts): string
    {
        $parts = array_values($parts);
        if(count($parts) <= 1):
            return (isset($parts[0]) ? (string) $parts[0] : '');
        endif;
        $last = array_pop($parts);

        return implode(', ', $parts).' and '.$last;
    }

    /**
     * Ids of the policies matched so far that have at least one exam which
     * cannot be drawn in full. An exam's share of each level depends on the
     * policy's own questions per test, so this is worked out here from the
     * preloaded counts (one query) rather than in SQL.
     */
    private function examGapIds($query): array
    {
        $ids = [];
        $candidates = (clone $query)
            ->select('policy_documents.id', 'policy_documents.questions_per_attempt')
            ->withDrawableCounts()
            ->get();
        foreach($candidates as $candidate):
            foreach(PolicyLevel::all() as $exam):
                if(!$candidate->examReady($exam)):
                    $ids[] = $candidate->id;
                    break;
                endif;
            endforeach;
        endforeach;

        return $ids;
    }

    /** The editable columns, normalised (blank max attempts = unlimited, blank time limit = untimed). */
    private function values(Request $request): array
    {
        $blankToNull = function ($value) {
            return (isset($value) && trim((string) $value) !== '' ? trim((string) $value) : null);
        };

        return [
            'policy_category_id' => (int) $request->policy_category_id,
            'title' => trim($request->title),
            'version' => $blankToNull($request->version),
            'pdf_url' => $blankToNull($request->pdf_url),
            'page_url' => $blankToNull($request->page_url),
            'description' => $blankToNull($request->description),
            'pass_mark' => (int) $request->pass_mark,
            'questions_per_attempt' => (int) $request->questions_per_attempt,
            'max_attempts' => (isset($request->max_attempts) && $request->max_attempts !== '' ? (int) $request->max_attempts : null),
            'time_limit_minutes' => (isset($request->time_limit_minutes) && $request->time_limit_minutes !== '' ? (int) $request->time_limit_minutes : null),
            'sort_order' => (isset($request->sort_order) && $request->sort_order !== '' ? (int) $request->sort_order : 0),
            'is_active' => ($request->input('is_active') == 1 ? 1 : 0),
        ];
    }

    /**
     * Tabulator sorters, whitelisted: unknown fields are dropped, dir is
     * asc|desc only. Default order follows the website: category, then policy.
     */
    private function applySorters($query, Request $request): void
    {
        $applied = false;
        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        foreach($sorters as $sort):
            $field = (is_array($sort) && isset($sort['field']) && is_string($sort['field']) ? $sort['field'] : '');
            $dir = (is_array($sort) && isset($sort['dir']) && strtolower((string) $sort['dir']) == 'desc' ? 'DESC' : 'ASC');
            if($field == 'category'):
                $query->orderBy(PolicyCategory::withTrashed()->select('name')->whereColumn('policy_categories.id', 'policy_documents.policy_category_id')->limit(1), $dir);
                $applied = true;
            elseif(isset(self::SORTABLE[$field])):
                $query->orderBy(self::SORTABLE[$field], $dir);
                $applied = true;
            endif;
        endforeach;

        if(!$applied):
            $query->orderBy(PolicyCategory::withTrashed()->select('sort_order')->whereColumn('policy_categories.id', 'policy_documents.policy_category_id')->limit(1), 'ASC')
                ->orderBy('sort_order', 'ASC')
                ->orderBy('title', 'ASC');
        endif;
        $query->orderBy('id', 'ASC');
    }
}
