<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HrVacancy;
use App\Models\PolicyAssignment;
use App\Models\PolicyAttempt;
use App\Models\PolicyAttemptAnswer;
use App\Models\PolicyAttemptEvent;
use App\Models\PolicyBadge;
use App\Models\PolicyDocument;
use App\Models\User;
use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * My HR › Policy Assessments — a member of staff's own policy tests.
 *
 * Every action works only on the signed-in employee's own assignments and
 * attempts; anything else is a 404, so ids cannot be probed. Nothing sent to
 * these pages says which answer is correct: the test page is built from a
 * whitelist of fields, and the result is the service's resultPayload() (score
 * and pass/fail, plus the badge a pass earned).
 *
 * Each assignment is one exam at one level (Beginner, Intermediate or
 * Expert), so the same policy can have more than one card. The list opens
 * with the badges the member of staff holds — their own, and only live ones.
 *
 * An exam draws a fixed mix of question levels (PolicyLevel::quota()). The
 * pages state that mix as counts; they never say which level a question is.
 *
 * A test is sat as a four-step wizard in its own exam shell:
 *   1 Briefing   take() with nothing in progress: the rules and the read
 *                declaration. Nothing is drawn and no clock runs; begin() is
 *                the only way an attempt starts.
 *   2 Questions  take() with an attempt in progress: every drawn question is
 *   3 Review     on the page and the script shows one at a time, then the
 *                review list. Leaving the tab or window is logged by event().
 *   4 Result     result(): a page of its own, built from what is stored, so
 *                it reads the same after a reload.
 * The countdown of a timed test starts from the server's own count of the
 * seconds left.
 */
class MyPolicyAssessmentController extends Controller
{
    /** Cover colours for policies without a thumbnail, one per category. */
    const COVER_TONES = 4;

    /** Short words left out of a policy's cover initials. */
    const INITIAL_STOPWORDS = ['policy', 'policies', 'procedure', 'procedures', 'and', 'of', 'the', 'for', 'on', 'to', 'a', 'an', 'in', 'with', '&', '-', '–'];

    /** Badges shown on the shelf before "Show all". */
    const SHELF_VISIBLE = 8;

    /** The countdown turns amber with this many seconds left… */
    const TIMER_WARN_SECONDS = 300;

    /** …unless the whole limit is this short or shorter: then it is amber for the last fifth of it. */
    const TIMER_SHORT_LIMIT_SECONDS = 600;
    const TIMER_SHORT_WARN_SHARE = 0.2;

    /** The countdown turns red with this many seconds left (never more than half the amber stretch). */
    const TIMER_DANGER_SECONDS = 60;

    /** Seconds left at which the time is read out to screen readers: 5 minutes and 1 minute. */
    const TIMER_ANNOUNCE_SECONDS = [300, 60];

    /** The longest "time away" the page may report for one exit (a day, in milliseconds); the service caps it further. */
    const MAX_AWAY_MS = 86400000;

    /** What the list says once an attempt left open past its time has been marked (the service's own wording). */
    const RAN_OUT_MESSAGE = 'The time for your last attempt ran out, so it has been marked. Start the test again when you are ready.';

    protected PolicyAssessmentService $service;

    public function __construct(PolicyAssessmentService $service)
    {
        $this->service = $service;
    }

    public function index(){
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);

        /* A timed test left open past its time is marked first, so the list
           never shows it as still in progress. */
        $this->service->finaliseExpiredAttempts((int) $employee->id);

        $assignments = PolicyAssignment::with([
                'openAttempt',
                /* Per-level drawable counts in the same query, so canAttempt()
                   needs no query per card. */
                'policy' => function ($query) {
                    $query->withDrawableCounts();
                },
                'policy.category',
                'badge',
            ])
            ->where('employee_id', $employee->id)
            /* Live assignments only: a policy HR has switched off drops out of
               the to-do list, but a pass on it stays visible as a record. */
            ->visibleToStaff()
            ->get();

        /* Two lookups for the whole list rather than one per card: the mix of
           each test in progress, and each test's last marked attempt (for the
           link to its result, and to say when it ended on the clock). */
        $openIds = [];
        foreach($assignments as $assignment):
            if($assignment->openAttempt):
                $openIds[] = (int) $assignment->openAttempt->id;
            endif;
        endforeach;
        $drawn = $this->drawnLevels($openIds);
        $lastMarked = $this->lastMarkedAttempts($assignments->pluck('id')->all());

        $grouped = $assignments->groupBy(function ($assignment) {
            return $assignment->policy->policy_category_id;
        })->sortBy(function ($rows) {
            $category = $rows->first()->policy->category;
            return sprintf('%010d|%s', (int) $category->sort_order, mb_strtolower((string) $category->name));
        });

        $sections = [];
        $summary = ['assigned' => 0, 'passed' => 0, 'overdue' => 0, 'in_progress' => 0, 'to_do' => 0, 'timed' => 0, 'percent' => 0];
        $number = 0;
        foreach($grouped as $rows):
            $number += 1;
            $category = $rows->first()->policy->category;
            /* The same policy at several levels sits together, easiest first. */
            $rows = $rows->sortBy(function ($assignment) {
                return sprintf('%010d|%s|%010d|%d', (int) $assignment->policy->sort_order, mb_strtolower((string) $assignment->policy->title), (int) $assignment->policy_document_id, $this->levelRank($assignment->levelKey()));
            });

            $cards = [];
            $passed = 0;
            foreach($rows as $assignment):
                $open = $assignment->openAttempt;
                $card = $this->card(
                    $assignment,
                    (($number - 1) % self::COVER_TONES) + 1,
                    ($open && isset($drawn[$open->id]) ? $drawn[$open->id] : null),
                    (isset($lastMarked[(int) $assignment->id]) ? $lastMarked[(int) $assignment->id] : null)
                );
                $cards[] = $card;

                $summary['assigned'] += 1;
                if($card['status'] == 'passed'):
                    $passed += 1;
                    $summary['passed'] += 1;
                else:
                    $summary['to_do'] += 1;
                endif;
                if($card['is_overdue']):
                    $summary['overdue'] += 1;
                endif;
                if($card['status'] == 'in_progress'):
                    $summary['in_progress'] += 1;
                endif;
                if($card['rules'] && $card['rules']['time_limit'] !== null):
                    $summary['timed'] += 1;
                endif;
            endforeach;

            $sections[] = [
                'number' => str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'name' => $category->name,
                'description' => $category->description,
                'cards' => $cards,
                'total' => count($cards),
                'passed' => $passed,
                'open' => ($passed < count($cards)),
            ];
        endforeach;

        $summary['percent'] = ($summary['assigned'] > 0 ? (int) floor($summary['passed'] / $summary['assigned'] * 100) : 0);

        $shelf = $this->badgeShelf($employee);

        return view('pages.users.my-account.policy-assessments.index', [
            'title' => 'Policy Assessments - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'My HR', 'href' => route('user.account')],
                ['label' => 'Policy Assessments', 'href' => 'javascript:void(0);'],
            ],
            'user' => User::find(auth()->user()->id),
            'employee' => $employee,
            'vacanties' => HrVacancy::where('active', 1)->get()->count(),
            'sections' => $sections,
            'summary' => $summary,
            'shelf' => $shelf,
        ]);
    }

    /**
     * Open the policy PDF and note that it was opened (first time only).
     */
    public function read(PolicyAssignment $assignment){
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);
        $this->ownAssignment($employee, $assignment);

        $policy = $assignment->policy;
        abort_unless($policy && !$policy->trashed(), 404);

        $url = (isset($policy->pdf_url) ? trim((string) $policy->pdf_url) : '');
        if(!$this->isWebUrl($url)):
            return redirect()->back(302, [], route('user.account.policy'))
                ->with('policyMessage', 'The document for "'.$policy->title.'" is not available online yet. Please contact HR.');
        endif;

        /* Conditional update, so the first opening is never overwritten. */
        PolicyAssignment::where('id', $assignment->id)->whereNull('policy_opened_at')->update(['policy_opened_at' => Carbon::now()]);

        return redirect()->away($url);
    }

    /**
     * The test page. With nothing in progress it is the Briefing (step 1) and
     * draws nothing — opening or reloading it never starts a test or a clock;
     * begin() does. With an attempt in progress it is the wizard's Questions
     * and Review steps, on the same questions in the same order.
     *
     * An attempt whose clock ran out while the page was closed is marked
     * here and the member of staff is sent back to the list with the reason;
     * a new one is never started in the same request.
     */
    public function take(PolicyAssignment $assignment){
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);
        $this->ownAssignment($employee, $assignment);

        $open = $this->openAttempt($assignment);
        $blocked = $this->blockedMessage($assignment, $open);
        if($blocked !== null):
            return $this->backToList($blocked);
        endif;

        if(!$open):
            return $this->briefing($assignment);
        endif;

        if($open->isPastGrace()):
            $this->service->finaliseExpiredAttempts((int) $employee->id);

            return $this->backToList($this->ranOutMessage($assignment));
        endif;

        /* A row that predates levels (or holds an unknown one) reads as Beginner;
           an attempt keeps the level its questions were drawn at. */
        $level = (PolicyLevel::isValid($open->level) ? $open->level : $this->examLevel($assignment));
        $questions = $this->questionsForStaff($open);
        $drawn = $this->drawnLevels([(int) $open->id]);
        $rules = $this->attemptRules($open, $level, (isset($drawn[$open->id]) ? $drawn[$open->id] : null));
        /* Exits already logged on this attempt (the page was reloaded part-way). */
        $activity = $this->service->tabActivity($open);
        $test = array_merge($rules, [
            'assignment_id' => $assignment->id,
            'attempt_id' => $open->id,
            'attempt_no' => (int) $open->attempt_no,
            'max_attempts' => $assignment->maxAttemptsAllowed(),
            'total' => count($questions),
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'questions' => $questions,
            'tab_exits' => (int) $activity['exits'],
        ]);

        return response()->view('pages.users.my-account.policy-assessments.take', array_merge($this->examPageData($assignment, $test['level_label']), [
            'examStep' => 2,
            'test' => $test,
            'timer' => $this->timerFor($open),
            'urls' => [
                'index' => route('user.account.policy'),
                'take' => route('user.account.policy.take', $assignment->id),
                'submit' => route('user.account.policy.submit', ['assignment' => $assignment->id, 'attempt' => $open->id]),
                'event' => route('user.account.policy.event', ['assignment' => $assignment->id, 'attempt' => $open->id]),
                'result' => route('user.account.policy.result', ['assignment' => $assignment->id, 'attempt' => $open->id]),
            ],
        ]))->header('Cache-Control', 'no-store, private');
    }

    /**
     * Start a test from its Briefing — the only way an attempt starts, timed
     * or not. The read declaration is given here, the questions are drawn,
     * and on a timed policy this is the moment the clock starts. Lands on the
     * test page, which then shows the attempt in progress.
     */
    public function begin(Request $request, PolicyAssignment $assignment){
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);
        $this->ownAssignment($employee, $assignment);

        $open = $this->openAttempt($assignment);
        $blocked = $this->blockedMessage($assignment, $open);
        if($blocked !== null):
            return $this->backToList($blocked);
        endif;

        $validator = Validator::make($request->all(), [
            'acknowledge' => 'accepted',
        ], [
            'acknowledge.accepted' => 'Please confirm that you have read and understood this policy before you start.',
        ]);
        if($validator->fails()):
            if($request->expectsJson()):
                return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            endif;

            return redirect()->route('user.account.policy.take', $assignment->id)->withErrors($validator);
        endif;

        PolicyAssignment::where('id', $assignment->id)->whereNull('acknowledged_at')->update(['acknowledged_at' => Carbon::now()]);

        try {
            $this->service->startOrResumeAttempt($assignment, $request->ip());
        } catch (ValidationException $e) {
            return $this->backToList($this->startFailureMessage($e, $assignment, $open));
        }

        return redirect()->route('user.account.policy.take', $assignment->id);
    }

    /**
     * The test page reports that the member of staff left the test tab or
     * window ('leave') or came back to it ('return'). The times recorded are
     * the server's; on a return the page also sends how long it measured
     * (away_ms), used only if the "left" signal never arrived.
     *
     * This is a record, not a gate: whatever is sent, and even if nothing
     * could be logged (the attempt is already marked, the input is junk),
     * the reply is a small 200 — the running totals — so the test carries on.
     * Only someone else's attempt is refused, as "not found".
     */
    public function event(Request $request, PolicyAssignment $assignment, PolicyAttempt $attempt){
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);
        $this->ownAssignment($employee, $assignment);
        $this->ownAttempt($employee, $assignment, $attempt);

        $phase = $request->input('phase');
        $type = ($request->input('type') === PolicyAttemptEvent::TYPE_TAB ? PolicyAttemptEvent::TYPE_TAB : PolicyAttemptEvent::TYPE_WINDOW);
        /* The question on screen (none on the Review step): a number this attempt does not have is not recorded. */
        $question = $this->wholeNumber($request->input('question'), max(1, (int) $attempt->total_questions));
        $awayMs = $this->wholeNumber($request->input('away_ms'), self::MAX_AWAY_MS, true);

        /* The exit this request closed, when it closed one. */
        $closed = null;
        try {
            if($phase === 'leave'):
                $this->service->logAttemptLeave($attempt, $type, $question);
            elseif($phase === 'return'):
                $closed = $this->service->logAttemptReturn($attempt, $awayMs, $type, $question);
            endif;
            $activity = $this->service->tabActivity($attempt);
        } catch (\Throwable $e) {
            Log::warning('Policy assessment: a tab exit could not be logged: '.$e->getMessage(), ['attempt_id' => $attempt->id]);
            $activity = ['exits' => (int) $attempt->tab_exits, 'away_seconds' => (int) $attempt->away_seconds];
        }

        return response()->json([
            'exits' => (int) $activity['exits'],
            'away_seconds' => (int) $activity['away_seconds'],
            'seconds' => ($closed && $closed->seconds !== null ? (int) $closed->seconds : null),
        ], 200)->header('Cache-Control', 'no-store, private');
    }

    /**
     * Mark the attempt. The reply carries the score and pass/fail only, plus
     * on a pass the badge it earned (level, policy title, date), and
     * 'result_url' — the Result page the test page then goes to.
     *
     * Every question must be answered, with two exceptions that mark what is
     * unanswered as wrong: the member of staff confirmed on the Review step
     * that they are handing in an unfinished test (confirm_incomplete), or it
     * is a timed attempt whose clock is at zero and the page is sending in
     * whatever was answered. The clock is the server's: a request that merely
     * says it timed out is still held to "every question".
     *
     * The read declaration is given at the Briefing; it is still stamped
     * here when sent, but no longer asked for.
     */
    public function submit(Request $request, PolicyAssignment $assignment, PolicyAttempt $attempt){
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);
        $this->ownAssignment($employee, $assignment);
        $this->ownAttempt($employee, $assignment, $attempt);

        $timeIsUp = $attempt->acceptsIncomplete();

        $rules = [];
        if(!$timeIsUp):
            $rules['answers'] = 'nullable|array';
        endif;
        $validator = Validator::make($request->all(), $rules, [
            'answers.array' => 'Please answer every question before submitting.',
        ]);
        if($validator->fails()):
            return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        endif;

        if($request->boolean('acknowledge')):
            PolicyAssignment::where('id', $assignment->id)->whereNull('acknowledged_at')->update(['acknowledged_at' => Carbon::now()]);
        endif;

        try {
            $result = $this->service->submitAttempt(
                $attempt,
                $this->answersFromRequest($attempt, $request->input('answers', [])),
                $request->boolean('confirm_incomplete')
            );
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $first = collect($errors)->flatten()->first();

            return response()->json(['message' => ($first ? $first : 'Please answer every question before submitting.'), 'errors' => $errors], 422);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }

        $result['result_url'] = route('user.account.policy.result', ['assignment' => $assignment->id, 'attempt' => $attempt->id]);

        return response()->json($result, 200);
    }

    /**
     * The Result step: a marked attempt as the member of staff may see it —
     * the score, pass or fail, what happens next and the tab activity that
     * was logged. Everything comes from what is stored, so the page reads the
     * same after a reload. An attempt still in progress goes back to the test.
     */
    public function result(PolicyAssignment $assignment, PolicyAttempt $attempt){
        $employee = $this->currentEmployee();
        abort_unless($employee, 404);
        $this->ownAssignment($employee, $assignment);
        $this->ownAttempt($employee, $assignment, $attempt);

        $policy = $assignment->policy;
        abort_unless($policy, 404);

        if($attempt->status != PolicyAttempt::STATUS_SUBMITTED):
            return redirect()->route('user.account.policy.take', $assignment->id);
        endif;

        $result = $this->service->resultPayload($attempt);
        $activity = $this->service->tabActivity($attempt);
        $total = (int) $result['total_questions'];
        $maxAttempts = $assignment->maxAttemptsAllowed();

        /* What happens next, as things stand now — this may be an older
           attempt, looked at again after a later one. */
        $open = $this->openAttempt($assignment);
        if($result['passed']):
            $next = 'passed';
        elseif($assignment->status == PolicyAssignment::STATUS_PASSED):
            $next = 'passed_since';
        elseif($open):
            $next = 'resume';
        elseif($result['can_retake']):
            $next = 'retake';
        elseif($result['attempts_left'] === 0):
            $next = 'locked';
        else:
            $next = 'unavailable';
        endif;

        $events = [];
        foreach($activity['events'] as $event):
            $events[] = [
                'at' => $event['at'],
                'where' => ($event['question_no'] !== null ? 'Question '.$event['question_no'] : 'Review screen'),
                'what' => $event['type_label'],
                'away' => ($event['seconds'] !== null ? $this->secondsLabel((int) $event['seconds']) : null),
            ];
        endforeach;

        $seconds = ($attempt->started_at && $attempt->submitted_at ? max(0, (int) $attempt->started_at->diffInSeconds($attempt->submitted_at)) : null);

        return response()->view('pages.users.my-account.policy-assessments.result', array_merge($this->examPageData($assignment, $result['level_label']), [
            'title' => 'Result: '.$policy->title.' ('.$result['level_label'].') - Policy Assessments - London Churchill College',
            'examStep' => 4,
            'result' => array_merge($result, [
                'attempt_id' => $attempt->id,
                'score_text' => $this->formatScore($result['score']),
                'score_ring' => max(0, min(100, (float) $result['score'])),
                'pass_needed' => $this->passNeeded($total, (int) $result['pass_mark']),
                'max_attempts' => $maxAttempts,
                'badge' => (isset($result['badge']) ? $result['badge'] : null),
                'badge_name' => PolicyLevel::badgeName($result['level']),
                'duration' => ($seconds !== null ? $this->secondsLabel($seconds, true) : null),
                'unanswered' => max(0, $total - (int) $result['answered_count']),
                'finished_on' => ($attempt->submitted_at ? $attempt->submitted_at->format('d M Y, H:i') : null),
                'next' => $next,
            ]),
            'activity' => [
                'exits' => (int) $activity['exits'],
                'away' => $this->secondsLabel((int) $activity['away_seconds']),
                'events' => $events,
            ],
            'urls' => [
                'index' => route('user.account.policy'),
                'badges' => route('user.account.policy').'#myhrPolicyBadges',
                'read' => route('user.account.policy.read', $assignment->id),
                'take' => route('user.account.policy.take', $assignment->id),
            ],
        ]))->header('Cache-Control', 'no-store, private');
    }

    protected function currentEmployee(){
        return Employee::where('user_id', auth()->user()->id)->first();
    }

    /** IDOR guard: someone else's assignment is simply "not found". */
    protected function ownAssignment(Employee $employee, PolicyAssignment $assignment){
        abort_unless($assignment->employee_id == $employee->id, 404);
    }

    /** The same for an attempt: it must be this employee's, on this assignment. */
    protected function ownAttempt(Employee $employee, PolicyAssignment $assignment, PolicyAttempt $attempt){
        abort_unless(($attempt->policy_assignment_id == $assignment->id && $attempt->employee_id == $employee->id), 404);
    }

    /**
     * A whole number from 1 to $max out of whatever the page sent, else NULL:
     * arrays, text, negatives and zero are all "not given". A larger number
     * is held at $max when $cap is set, and is "not given" otherwise.
     */
    protected function wholeNumber($value, int $max, bool $cap = false): ?int
    {
        if(!is_scalar($value) || !is_numeric($value)):
            return null;
        endif;

        $number = (float) $value;
        if(!is_finite($number) || $number < 1):
            return null;
        endif;
        if($number > $max):
            return ($cap ? $max : null);
        endif;

        return (int) $number;
    }

    protected function backToList(string $message){
        return redirect()->route('user.account.policy')->with('policyMessage', $message);
    }

    /** The attempt being sat on this assignment, if any. */
    protected function openAttempt(PolicyAssignment $assignment): ?PolicyAttempt
    {
        return PolicyAttempt::where('policy_assignment_id', $assignment->id)
            ->where('status', PolicyAttempt::STATUS_IN_PROGRESS)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /** The assignment's level; a row that predates levels (or holds an unknown one) reads as Beginner. */
    protected function examLevel(PolicyAssignment $assignment): string
    {
        return (PolicyLevel::isValid($assignment->levelKey()) ? $assignment->levelKey() : PolicyLevel::BEGINNER);
    }

    /**
     * Why this test cannot be opened, as the message for the list — or NULL
     * when it can. An attempt already in progress can always be reopened,
     * whatever has changed since it was drawn.
     */
    protected function blockedMessage(PolicyAssignment $assignment, ?PolicyAttempt $open): ?string
    {
        $policy = $assignment->policy;
        if(!$policy || $policy->trashed() || !$policy->category):
            return 'This policy is no longer available.';
        endif;

        $examName = PolicyLevel::label($this->examLevel($assignment)).' exam';

        if($assignment->status == PolicyAssignment::STATUS_PASSED):
            return 'You have already passed the '.$examName.' for "'.$policy->title.'". There is nothing more to do.';
        endif;

        if(!$open && !$assignment->canAttempt()):
            if($assignment->attemptsLeft() === 0):
                return 'You have no attempts left on the '.$examName.' for "'.$policy->title.'". Please contact HR if you need another attempt.';
            endif;

            return 'The '.$examName.' for "'.$policy->title.'" is not available yet. Please check back later.';
        endif;

        return null;
    }

    /**
     * What to say when a test would not start. The service's own message is
     * used as it stands — including "the time ran out" for an attempt left
     * open past its time, which is marked there and then; a new attempt is
     * never started in the same request. Only when that used up the last
     * attempt is the "start again" part replaced, since there is nothing to
     * start.
     */
    protected function startFailureMessage(ValidationException $e, PolicyAssignment $assignment, ?PolicyAttempt $open): string
    {
        $first = collect($e->errors())->flatten()->first();
        $message = ($first ? (string) $first : 'This test is not available.');

        if($open && $open->isPastGrace()):
            return $this->ranOutMessage($assignment, $message);
        endif;

        return $message;
    }

    /**
     * What to say once an attempt left open past its time has been marked:
     * $message (by default, that the test can be started again) — unless
     * that was the last attempt, when there is nothing left to start.
     */
    protected function ranOutMessage(PolicyAssignment $assignment, string $message = self::RAN_OUT_MESSAGE): string
    {
        $fresh = PolicyAssignment::find($assignment->id);
        if($fresh && $fresh->status != PolicyAssignment::STATUS_PASSED && $fresh->attemptsLeft() === 0):
            return 'The time for your last attempt ran out, so it has been marked. You have no attempts left on this test. Please contact HR if you need another attempt.';
        endif;

        return $message;
    }

    /**
     * The Briefing (step 1) of a test, timed or not: the rules, the facts and
     * the read declaration, with nothing drawn and no clock running.
     */
    protected function briefing(PolicyAssignment $assignment){
        $policy = $assignment->policy;
        $level = $this->examLevel($assignment);
        $rules = $this->examRules($policy, $level);
        $test = array_merge($rules, [
            'assignment_id' => $assignment->id,
            'attempt_no' => (int) $assignment->attempts_count + 1,
            'max_attempts' => $assignment->maxAttemptsAllowed(),
            'attempts_left' => $assignment->attemptsLeft(),
            'pass_needed' => $this->passNeeded((int) $rules['question_count'], (int) $rules['pass_mark']),
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'badge_name' => PolicyLevel::badgeName($level),
        ]);

        return response()->view('pages.users.my-account.policy-assessments.start', array_merge($this->examPageData($assignment, $test['level_label']), [
            'examStep' => 1,
            'test' => $test,
            'urls' => [
                'index' => route('user.account.policy'),
                'read' => route('user.account.policy.read', $assignment->id),
                'begin' => route('user.account.policy.begin', $assignment->id),
            ],
        ]))->header('Cache-Control', 'no-store, private');
    }

    /** What the three exam pages share: the page title and the policy's details. */
    protected function examPageData(PolicyAssignment $assignment, string $levelLabel): array
    {
        $policy = $assignment->policy;
        $dueDate = ($assignment->due_date ? $assignment->due_date : null);

        return [
            'title' => $policy->title.' ('.$levelLabel.') - Policy Assessments - London Churchill College',
            'policy' => [
                'title' => $policy->title,
                'version' => $policy->version,
                'category' => (isset($policy->category->name) ? $policy->category->name : ''),
                'has_pdf' => $this->isWebUrl(trim((string) $policy->pdf_url)),
                'due_date' => ($dueDate ? $dueDate->format('d M Y') : null),
                'is_overdue' => $assignment->isOverdue(),
            ],
        ];
    }

    /**
     * How many questions out of $total must be right to reach $passMark —
     * worked out the way the service marks (the score is rounded to two
     * decimal places, then compared), so "8 of 10" is never one out.
     */
    protected function passNeeded(int $total, int $passMark): int
    {
        if($total <= 0):
            return 0;
        endif;

        for($right = 0; $right <= $total; $right++):
            if(round($right / $total * 100, 2) >= $passMark):
                return $right;
            endif;
        endfor;

        return $total;
    }

    /**
     * A length of time in words: 5 -> "5s", 65 -> "1m 05s", 3725 -> "1h 02m 05s".
     * With $always the minutes are shown even under a minute ("0m 45s").
     */
    protected function secondsLabel(int $seconds, bool $always = false): string
    {
        $seconds = max(0, $seconds);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $rest = $seconds % 60;

        if($hours > 0):
            return $hours.'h '.sprintf('%02d', $minutes).'m '.sprintf('%02d', $rest).'s';
        endif;
        if($minutes > 0 || $always):
            return $minutes.'m '.sprintf('%02d', $rest).'s';
        endif;

        return $rest.'s';
    }

    /**
     * The rules of the next test on a policy, as the pages state them: how
     * many questions, the pass mark, the time limit (NULL when untimed) and
     * the mix of question levels — the counts PolicyLevel::quota() gives for
     * this exam level, so the pattern is never written out by hand here.
     */
    protected function examRules(PolicyDocument $policy, string $level): array
    {
        $questions = max(1, (int) $policy->questions_per_attempt);
        $limit = ($policy->isTimed() ? (int) $policy->time_limit_minutes : null);

        return $this->rules($questions, (int) $policy->pass_mark, $limit, PolicyLevel::quota($level, $questions));
    }

    /**
     * The rules a test in progress was started under — its own snapshot, not
     * the policy as it stands now. $drawn is [question level => count] for
     * the questions it holds (see drawnLevels()); NULL when that is not
     * known, and then no mix is stated.
     */
    protected function attemptRules(PolicyAttempt $attempt, string $level, ?array $drawn): array
    {
        $limit = ($attempt->isTimed() && $attempt->time_limit_minutes !== null ? (int) $attempt->time_limit_minutes : null);

        return $this->rules((int) $attempt->total_questions, (int) $attempt->pass_mark, $limit, ($drawn !== null ? $drawn : []));
    }

    /**
     * $counts is [question level => count]. 'pattern' keeps the levels that
     * have questions, easiest first; 'pattern_text' is the same as one line
     * ("7 Beginner · 2 Intermediate · 1 Expert").
     */
    protected function rules(int $questions, int $passMark, ?int $limit, array $counts): array
    {
        $pattern = [];
        $parts = [];
        foreach(PolicyLevel::all() as $level):
            $count = (isset($counts[$level]) ? (int) $counts[$level] : 0);
            if($count <= 0):
                continue;
            endif;
            $pattern[] = ['level' => $level, 'label' => PolicyLevel::label($level), 'count' => $count];
            $parts[] = $count.' '.PolicyLevel::label($level);
        endforeach;

        return [
            'question_count' => $questions,
            'pass_mark' => $passMark,
            'time_limit' => $limit,
            'time_limit_label' => ($limit !== null ? $this->minutesLabel($limit) : null),
            'time_limit_words' => ($limit !== null ? $this->minutesLabel($limit, true) : null),
            'pattern' => $pattern,
            'pattern_text' => implode(' · ', $parts),
        ];
    }

    /** 15 -> "15 min" ("15 minutes" in words); 90 -> "1 hr 30 min" ("1 hour 30 minutes"). */
    protected function minutesLabel(int $minutes, bool $words = false): string
    {
        $minutes = max(0, $minutes);
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        $parts = [];
        if($hours > 0):
            $parts[] = $hours.' '.($words ? ($hours == 1 ? 'hour' : 'hours') : 'hr');
        endif;
        if($rest > 0 || $hours == 0):
            $parts[] = $rest.' '.($words ? ($rest == 1 ? 'minute' : 'minutes') : 'min');
        endif;

        return implode(' ', $parts);
    }

    /**
     * How many questions of each level these attempts hold:
     * [attempt id => [question level => count]], one query for the lot.
     * An attempt drawn before question levels were recorded is left out, so
     * nothing is claimed about its mix. Only ever used as totals — the pages
     * do not say which level a question is.
     */
    protected function drawnLevels(array $attemptIds): array
    {
        if(empty($attemptIds)):
            return [];
        endif;

        $rows = PolicyAttemptAnswer::whereIn('policy_attempt_id', $attemptIds)
            ->selectRaw('policy_attempt_id, question_level, COUNT(*) as aggregate')
            ->groupBy('policy_attempt_id', 'question_level')
            ->get();

        $drawn = [];
        $unknown = [];
        foreach($rows as $row):
            $attemptId = (int) $row->policy_attempt_id;
            if(!PolicyLevel::isValid($row->question_level)):
                $unknown[$attemptId] = true;
                continue;
            endif;
            $drawn[$attemptId][(string) $row->question_level] = (int) $row->aggregate;
        endforeach;

        return array_diff_key($drawn, $unknown);
    }

    /**
     * The most recent marked attempt of each of these assignments:
     * [assignment id => ['id' => attempt id, 'timed_out' => bool]], one query
     * for the lot. The card links to its result, and says so when it ended
     * because the time ran out.
     */
    protected function lastMarkedAttempts(array $assignmentIds): array
    {
        if(empty($assignmentIds)):
            return [];
        endif;

        $rows = PolicyAttempt::whereIn('id', function ($query) use ($assignmentIds) {
                $query->selectRaw('MAX(id)')
                    ->from('policy_attempts')
                    ->whereIn('policy_assignment_id', $assignmentIds)
                    ->where('status', PolicyAttempt::STATUS_SUBMITTED)
                    ->whereNull('deleted_at')
                    ->groupBy('policy_assignment_id');
            })
            ->get(['id', 'policy_assignment_id', 'timed_out']);

        $last = [];
        foreach($rows as $row):
            $last[(int) $row->policy_assignment_id] = ['id' => (int) $row->id, 'timed_out' => (bool) $row->timed_out];
        endforeach;

        return $last;
    }

    /**
     * The countdown for a timed attempt, NULL for an untimed one. 'seconds'
     * is the server's count of the time left as the page is built; the page
     * counts down from it and never works out the deadline from its own
     * clock. 'warn' and 'danger' are the seconds left at which the countdown
     * turns amber and red.
     */
    protected function timerFor(PolicyAttempt $attempt): ?array
    {
        if(!$attempt->isTimed()):
            return null;
        endif;

        $seconds = (int) $attempt->secondsRemaining();
        $limit = ($attempt->started_at ? (int) $attempt->started_at->diffInSeconds($attempt->expires_at) : 0);
        if($limit <= 0):
            $limit = max(60, (int) $attempt->time_limit_minutes * 60, $seconds);
        endif;

        $warn = ($limit > self::TIMER_SHORT_LIMIT_SECONDS ? self::TIMER_WARN_SECONDS : (int) floor($limit * self::TIMER_SHORT_WARN_SHARE));
        $danger = min(self::TIMER_DANGER_SECONDS, intdiv($warn, 2));

        /* Only the marks the clock can still cross are worth announcing. */
        $announce = [];
        foreach(self::TIMER_ANNOUNCE_SECONDS as $mark):
            if($mark < $limit):
                $announce[] = $mark;
            endif;
        endforeach;

        $state = 'ok';
        if($seconds <= 0):
            $state = 'done';
        elseif($seconds <= $danger):
            $state = 'danger';
        elseif($seconds <= $warn):
            $state = 'warn';
        endif;

        return [
            'seconds' => $seconds,
            'limit' => $limit,
            'warn' => $warn,
            'danger' => $danger,
            'announce' => $announce,
            'clock' => sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60),
            'state' => $state,
            'percent' => (int) max(0, min(100, round($seconds / $limit * 100))),
        ];
    }

    /**
     * The drawn questions as the staff page may see them: text and option
     * text only, in the shuffled order stored at draw time.
     *
     * Each option is sent as a letter (a, b, c…) — its position in that
     * shuffled order — rather than its database id. Option ids follow the
     * order HR typed the options in, so they could hint at the answer (for
     * example if the correct option is usually written first); a position
     * in a shuffled list says nothing.
     */
    protected function questionsForStaff(PolicyAttempt $attempt): array
    {
        $rows = PolicyAttemptAnswer::where('policy_attempt_id', $attempt->id)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->get(['id', 'sort_order', 'question_text', 'option_order', 'options_snapshot']);

        $questions = [];
        $number = 0;
        foreach($rows as $row):
            $texts = [];
            foreach((is_array($row->options_snapshot) ? $row->options_snapshot : []) as $option):
                if(isset($option['id'])):
                    $texts[(int) $option['id']] = (isset($option['text']) ? (string) $option['text'] : '');
                endif;
            endforeach;

            $options = [];
            $position = 0;
            foreach((is_array($row->option_order) ? array_values($row->option_order) : []) as $optionId):
                $letter = chr(ord('a') + $position);
                $position += 1;
                $options[] = [
                    'value' => $letter,
                    'letter' => strtoupper($letter),
                    'text' => (isset($texts[(int) $optionId]) ? $texts[(int) $optionId] : ''),
                ];
            endforeach;

            $number += 1;
            $questions[] = [
                'row_id' => (int) $row->id,
                'number' => $number,
                'text' => (string) $row->question_text,
                'options' => $options,
            ];
        endforeach;

        return $questions;
    }

    /**
     * Turn the posted answers ([answer row id => letter]) into the
     * [answer row id => option id] the service marks. A letter maps to the
     * option at that position of the row's stored order; a numeric value is
     * passed through as an option id. The service rejects anything that is
     * not one of the options that question was shown with.
     */
    protected function answersFromRequest(PolicyAttempt $attempt, $input): array
    {
        $answers = [];
        if(!is_array($input)):
            return $answers;
        endif;

        $rows = PolicyAttemptAnswer::where('policy_attempt_id', $attempt->id)->get(['id', 'option_order']);
        foreach($rows as $row):
            if(!isset($input[$row->id]) || !is_scalar($input[$row->id])):
                continue;
            endif;

            $value = strtolower(trim((string) $input[$row->id]));
            if(preg_match('/^[a-z]$/', $value)):
                $order = (is_array($row->option_order) ? array_values($row->option_order) : []);
                $index = ord($value) - ord('a');
                $answers[$row->id] = (isset($order[$index]) ? (int) $order[$index] : 0);
            else:
                $answers[$row->id] = $value;
            endif;
        endforeach;

        return $answers;
    }

    /**
     * Everything a policy card on the list shows. $drawn is the mix of the
     * test in progress, when there is one and it is known (see drawnLevels());
     * $lastMarked is the last marked attempt, if any (see lastMarkedAttempts()).
     */
    protected function card(PolicyAssignment $assignment, int $tone, ?array $drawn = null, ?array $lastMarked = null): array
    {
        $policy = $assignment->policy;
        $ranOut = ($lastMarked !== null && $lastMarked['timed_out']);
        $status = $assignment->display_status;
        $open = $assignment->openAttempt;
        $hasOpen = $assignment->hasOpenAttempt();
        $maxAttempts = $assignment->maxAttemptsAllowed();

        $action = null;
        $reason = null;
        if($status != 'passed'):
            if($hasOpen):
                $action = ['type' => 'resume', 'label' => 'Resume test', 'icon' => 'play-circle'];
            elseif($assignment->canAttempt()):
                $action = ($assignment->status == PolicyAssignment::STATUS_FAILED
                    ? ['type' => 'retake', 'label' => 'Retake test', 'icon' => 'rotate-ccw']
                    : ['type' => 'start', 'label' => 'Start test', 'icon' => 'pen-line']);
            elseif($assignment->attemptsLeft() === 0):
                $reason = 'No attempts left — contact HR';
            else:
                $reason = 'Test not available yet';
            endif;
        endif;

        $thumbnail = (isset($policy->thumbnail_url) ? trim((string) $policy->thumbnail_url) : '');
        /* An unknown stored level reads as Beginner, as the level pill draws it. */
        $level = $this->examLevel($assignment);

        /* The live badge for this policy at this level — shown on a passed
           card unless HR has revoked it. */
        $badge = null;
        if($status == 'passed' && $assignment->badge):
            $badge = [
                'level' => $level,
                'badge_name' => PolicyLevel::badgeName($level),
            ];
        endif;

        /* The rules of the test still to be sat: the one in progress as it was
           started, otherwise the next one as the policy stands. A passed test
           has nothing left to sit, so its card states none. */
        $rules = null;
        $clock = null;
        if($status != 'passed'):
            if($open):
                $openLevel = (PolicyLevel::isValid($open->level) ? $open->level : $level);
                $rules = $this->attemptRules($open, $openLevel, $drawn);
                if($open->isTimed()):
                    $left = (int) $open->secondsRemaining();
                    $clock = ($left > 0
                        ? 'The clock is running — '.($left < 60 ? 'less than a minute' : 'about '.$this->minutesLabel((int) ceil($left / 60))).' left'
                        : 'Time is up — open the test to finish it');
                endif;
            else:
                $rules = $this->examRules($policy, $level);
            endif;
        endif;

        return [
            'id' => $assignment->id,
            'title' => $policy->title,
            'version' => $policy->version,
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'badge' => $badge,
            'status' => $status,
            'status_label' => $assignment->display_status_label,
            'due_date' => ($assignment->due_date ? $assignment->due_date->format('d M Y') : null),
            'is_overdue' => $assignment->isOverdue(),
            'best_score' => ($assignment->best_score !== null ? $this->formatScore($assignment->best_score) : null),
            'attempts_count' => (int) $assignment->attempts_count,
            'max_attempts' => $maxAttempts,
            'passed_at' => ($assignment->passed_at ? $assignment->passed_at->format('d M Y') : null),
            'opened_at' => ($assignment->policy_opened_at ? $assignment->policy_opened_at->format('d M Y') : null),
            'thumbnail' => ($this->isWebUrl($thumbnail) ? $thumbnail : null),
            'initials' => $this->initials((string) $policy->title),
            'tone' => $tone,
            'has_pdf' => $this->isWebUrl(trim((string) $policy->pdf_url)),
            'read_url' => route('user.account.policy.read', $assignment->id),
            'take_url' => route('user.account.policy.take', $assignment->id),
            'result_url' => ($lastMarked !== null ? route('user.account.policy.result', ['assignment' => $assignment->id, 'attempt' => $lastMarked['id']]) : null),
            'action' => $action,
            'reason' => $reason,
            'rules' => $rules,
            'clock' => $clock,
            'ran_out' => ($ranOut && $status != 'passed' && !$hasOpen),
        ];
    }

    /**
     * The "My badges" shelf: this employee's live (not revoked) badges on
     * policies that still exist — the same rule as the service's
     * badgeCounts(), so HR and the member of staff see the same number.
     * Hardest level first (gold, silver, bronze), newest first within a level.
     */
    protected function badgeShelf(Employee $employee): array
    {
        $badges = PolicyBadge::with('policy')
            ->where('employee_id', $employee->id)
            ->whereHas('policy', function ($query) {
                $query->whereNull('policy_documents.deleted_at');
            })
            ->get()
            ->sort(function ($a, $b) {
                $byLevel = $this->levelRank((string) $b->level) <=> $this->levelRank((string) $a->level);
                if($byLevel !== 0):
                    return $byLevel;
                endif;
                $aTime = ($a->awarded_at ? $a->awarded_at->getTimestamp() : 0);
                $bTime = ($b->awarded_at ? $b->awarded_at->getTimestamp() : 0);
                if($aTime !== $bTime):
                    return $bTime <=> $aTime;
                endif;

                return (int) $b->id <=> (int) $a->id;
            })
            ->values();

        $counts = [];
        foreach(PolicyLevel::all() as $level):
            $counts[$level] = 0;
        endforeach;

        $items = [];
        foreach($badges as $badge):
            $level = (PolicyLevel::isValid($badge->level) ? (string) $badge->level : PolicyLevel::BEGINNER);
            $counts[$level] += 1;
            $items[] = [
                'level' => $level,
                'level_label' => PolicyLevel::label($level),
                'badge_name' => PolicyLevel::badgeName($level),
                'title' => ($badge->policy ? (string) $badge->policy->title : ''),
                'awarded_at' => ($badge->awarded_at ? $badge->awarded_at->format('d M Y') : null),
                'awarded_iso' => ($badge->awarded_at ? $badge->awarded_at->format('Y-m-d') : null),
                'score' => ($badge->score !== null ? $this->formatScore($badge->score) : null),
            ];
        endforeach;

        /* Tally, hardest first to match the shelf. */
        $tally = [];
        foreach(array_reverse(PolicyLevel::all()) as $level):
            $tally[] = [
                'level' => $level,
                'level_label' => PolicyLevel::label($level),
                'badge_name' => PolicyLevel::badgeName($level),
                'count' => $counts[$level],
            ];
        endforeach;

        return [
            'items' => $items,
            'total' => count($items),
            'tally' => $tally,
            'visible' => self::SHELF_VISIBLE,
        ];
    }

    /** 0 for Beginner, 1 for Intermediate, 2 for Expert; an unknown level sorts first. */
    protected function levelRank(string $level): int
    {
        $rank = array_search($level, PolicyLevel::all(), true);

        return ($rank === false ? -1 : (int) $rank);
    }

    /** Only plain web links are ever followed or shown as images. */
    protected function isWebUrl(string $url): bool
    {
        if($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false):
            return false;
        endif;

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    /** 66.67 stays 66.67 (rounding it to 67 would read as a pass at 67%); 100.00 becomes 100. */
    protected function formatScore($score): string
    {
        $text = number_format((float) $score, 2, '.', '');
        if(str_contains($text, '.')):
            $text = rtrim(rtrim($text, '0'), '.');
        endif;

        return $text;
    }

    /** "Adverse Occurrence Policy" -> "AO", for the plain cover. */
    protected function initials(string $title): string
    {
        $words = preg_split('/[\s\/,]+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY);
        $letters = '';
        foreach($words as $word):
            if(in_array(mb_strtolower($word), self::INITIAL_STOPWORDS, true)):
                continue;
            endif;
            $first = mb_substr(preg_replace('/[^\p{L}\p{N}]/u', '', $word), 0, 1);
            if($first !== ''):
                $letters .= mb_strtoupper($first);
            endif;
            if(mb_strlen($letters) >= 2):
                break;
            endif;
        endforeach;

        return ($letters !== '' ? $letters : 'LCC');
    }
}
