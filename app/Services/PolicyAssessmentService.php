<?php

namespace App\Services;

use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\PolicyAssignment;
use App\Models\PolicyAttempt;
use App\Models\PolicyAttemptAnswer;
use App\Models\PolicyAttemptEvent;
use App\Models\PolicyBadge;
use App\Models\PolicyDocument;
use App\Models\PolicyQuestion;
use App\Models\PolicyRole;
use App\Support\PolicyLevel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

/**
 * Everything Policy Assessments decides, in one place, so the HR screens, the
 * staff screens and the reminder cron cannot disagree about what a result
 * means: who gets assigned what, how a test is drawn, how it is marked, and
 * what the member of staff is told afterwards.
 *
 * Staff are only ever told their score and whether they passed — never which
 * answers were right. Nothing returned for a staff page carries is_correct,
 * correct_option_id, explanation or source_excerpt.
 *
 * Levels: every assignment is set at one level (PolicyLevel — Beginner,
 * Intermediate, Expert). Its tests draw that level's mix of questions from
 * all three levels (PolicyLevel::MIX) and are only available when every
 * level can fill its share. Its first pass earns that level's badge (bronze,
 * silver, gold). One live assignment and one live badge per (employee,
 * policy, level).
 *
 * Roles: HR assigns by policy role (PolicyRole — "Lecturer", "Finance"), a
 * named set of ticked policies. A role only chooses the policies at the
 * moment of assigning; changing its ticks later changes no assignment.
 *
 * Timing: a policy may set a time limit. The clock is the server's, starts
 * when the attempt is drawn, and the attempt is marked when it runs out.
 */
class PolicyAssessmentService
{
    const FROM_EMAIL = 'hr@lcc.ac.uk';
    const FROM_NAME = 'London Churchill College';

    /** A cap on logged tab exits per attempt, so a looping script cannot fill the table. */
    const MAX_EVENTS_PER_ATTEMPT = 200;

    /** Days ahead of the due date that a reminder starts, and the gap between reminders. */
    const REMINDER_WINDOW_DAYS = 3;

    public static function canManage(): bool
    {
        $user = auth()->user();
        if(!$user):
            return false;
        endif;

        $priv = $user->priv();

        return isset($priv['policy_assessment_manage']) && $priv['policy_assessment_manage'] == 1;
    }

    /**
     * Assign policies — by policy role, picked individually and/or as whole
     * categories — to one or more members of staff, as a test at $level.
     *
     * What gets assigned: an explicit choice ($policyIds, $categoryIds) is
     * the source of truth — the assign screen fills its policy list from the
     * role and HR may then add or remove policies, so that list is assigned
     * exactly as sent. $roleIds on their own (no policies, no categories)
     * assign every policy those roles currently cover.
     *
     * A role or a category means its active, non-deleted policies as they
     * stand now; policies added to it later are not picked up automatically.
     * Only live, active roles count — an inactive, archived or unknown role
     * id is ignored. An employee who already has a live assignment for a
     * policy at this level is not given a second one: a new due date is
     * applied to it (unless it is already passed), otherwise it is left
     * alone. The same policy at a different level is a separate assignment.
     *
     * Each new assignment remembers the role it came through
     * (policy_role_id): the first of $roleIds, in the order given, that
     * covers the policy; NULL for a policy no given role covers — one HR
     * added by hand. An assignment that already exists is never re-labelled.
     *
     * With $sendEmail each employee gets one email listing only the
     * assignments that were newly created for them.
     *
     * @throws ValidationException on 'level' when $level is not a PolicyLevel key.
     * @return array ['created' => n, 'updated' => n, 'skipped' => n, 'emailed' => n]
     */
    public function assign(array $employeeIds, array $policyIds, array $categoryIds, ?string $dueDate, bool $sendEmail, ?string $note = null, string $level = PolicyLevel::BEGINNER, array $roleIds = []): array
    {
        if(!PolicyLevel::isValid($level)):
            throw ValidationException::withMessages(['level' => 'Please choose a level: Beginner, Intermediate or Expert.']);
        endif;

        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'emailed' => 0];

        $employeeIds = $this->cleanIds($employeeIds);
        $policyIds = $this->cleanIds($policyIds);
        $categoryIds = $this->cleanIds($categoryIds);

        /* policy id => the first given role that covers it. */
        $roleFor = $this->rolePolicyMap($this->cleanIds($roleIds));
        if(empty($policyIds) && empty($categoryIds)):
            $policyIds = array_keys($roleFor);
        endif;

        if(!empty($categoryIds)):
            $categoryPolicyIds = PolicyDocument::whereIn('policy_category_id', $categoryIds)
                ->where('is_active', 1)
                ->whereHas('category')
                ->pluck('id')
                ->all();
            $policyIds = array_merge($policyIds, $categoryPolicyIds);
        endif;

        /* Only live rows: a deleted policy or employee is never assigned. */
        $policyIds = (!empty($policyIds) ? PolicyDocument::whereIn('id', array_unique($policyIds))->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->pluck('id')->all() : []);
        $employeeIds = (!empty($employeeIds) ? Employee::whereIn('id', $employeeIds)->pluck('id')->all() : []);

        if(empty($policyIds) || empty($employeeIds)):
            return $result;
        endif;

        $dueDate = $this->normaliseDate($dueDate);
        $note = (isset($note) && trim($note) !== '' ? trim($note) : null);
        $userId = auth()->id();
        $now = Carbon::now();

        /* employee_id => [assignment ids created for them] */
        $createdFor = [];

        /* Up to 3 tries: InnoDB can still pick a deadlock victim when two
           assigns share neither a policy nor a member of staff (their gap
           locks on policy_assignments can overlap). The victim is rolled back
           in full and simply runs again. */
        DB::transaction(function () use ($employeeIds, $policyIds, $dueDate, $note, $level, $roleFor, $userId, $now, &$result, &$createdFor) {
            /* A retried run starts from nothing. */
            $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'emailed' => 0];
            $createdFor = [];

            /* Take the policies, then the staff, in id order (record locks, so
               no gaps): two assigns that share a policy or a member of staff
               queue behind each other here instead of deadlocking below. */
            PolicyDocument::whereIn('id', $policyIds)->orderBy('id', 'ASC')->lockForUpdate()->pluck('id');
            Employee::whereIn('id', $employeeIds)->orderBy('id', 'ASC')->lockForUpdate()->pluck('id');

            /* Locking read: holds the (employee, policy, level) index range, so
               two HR users assigning the same pair at once cannot both insert. */
            $existing = PolicyAssignment::whereIn('employee_id', $employeeIds)
                ->whereIn('policy_document_id', $policyIds)
                ->where('level', $level)
                ->lockForUpdate()
                ->get()
                ->keyBy(function ($row) {
                    return $row->employee_id.'-'.$row->policy_document_id;
                });

            foreach($employeeIds as $employeeId):
                foreach($policyIds as $policyId):
                    $key = $employeeId.'-'.$policyId;
                    if(isset($existing[$key])):
                        $assignment = $existing[$key];
                        $currentDue = ($assignment->due_date ? $assignment->due_date->format('Y-m-d') : null);
                        if($assignment->status != PolicyAssignment::STATUS_PASSED && $dueDate !== null && $currentDue !== $dueDate):
                            $assignment->due_date = $dueDate;
                            $assignment->updated_by = $userId;
                            $assignment->save();
                            $result['updated'] += 1;
                        else:
                            $result['skipped'] += 1;
                        endif;
                    else:
                        $assignment = PolicyAssignment::create([
                            'employee_id' => $employeeId,
                            'policy_document_id' => $policyId,
                            'level' => $level,
                            'policy_role_id' => (isset($roleFor[$policyId]) ? $roleFor[$policyId] : null),
                            'assigned_by' => $userId,
                            'assigned_at' => $now,
                            'due_date' => $dueDate,
                            'status' => PolicyAssignment::STATUS_PENDING,
                            'attempts_count' => 0,
                            'extra_attempts' => 0,
                            'note' => $note,
                            'created_by' => $userId,
                        ]);
                        $createdFor[$employeeId][] = $assignment->id;
                        $result['created'] += 1;
                    endif;
                endforeach;
            endforeach;
        }, 3);

        if($sendEmail && !empty($createdFor)):
            foreach($createdFor as $employeeId => $assignmentIds):
                $employee = Employee::with('employment')->find($employeeId);
                if(!$employee):
                    continue;
                endif;
                $assignments = PolicyAssignment::with('policy.category')->whereIn('id', $assignmentIds)->get();
                if($this->sendAssignmentEmail($employee, $assignments)):
                    $result['emailed'] += 1;
                endif;
            endforeach;
        endif;

        return $result;
    }

    /**
     * Replace the policies ticked for a role with $policyIds.
     *
     * Only policies that have not been deleted can be ticked: a deleted or
     * unknown id is ignored. A switched-off policy can be ticked — it simply
     * is not assigned until it is switched on (PolicyRole::activePolicies()).
     * A tick on a policy that has since been archived is left as it is: the
     * role's edit screen cannot show it, so saving the role must not quietly
     * lose it, and it counts again if the policy is restored.
     *
     * Assignments already made through the role are not touched — a role is
     * a shortcut for choosing policies, not a live link to them.
     *
     * @throws ModelNotFoundException when the role no longer exists.
     */
    public function syncRolePolicies(PolicyRole $role, array $policyIds): void
    {
        $policyIds = $this->cleanIds($policyIds);
        $userId = auth()->id();

        DB::transaction(function () use ($role, $policyIds, $userId) {
            /* One save per role at a time. The pivot has no unique index, so
               this lock is what stops two saves ticking a policy twice. */
            $locked = PolicyRole::withTrashed()->where('id', $role->id)->lockForUpdate()->first();
            if(!$locked):
                throw (new ModelNotFoundException())->setModel(PolicyRole::class, [$role->id]);
            endif;

            /* What is ticked now, oldest row first; a stray second row for
               the same policy is cleared away. */
            $ticked = [];
            $duplicates = [];
            foreach(DB::table(PolicyRole::PIVOT_TABLE)->where('policy_role_id', $locked->id)->orderBy('id', 'ASC')->get(['id', 'policy_document_id']) as $row):
                if(isset($ticked[(int) $row->policy_document_id])):
                    $duplicates[] = (int) $row->id;
                else:
                    $ticked[(int) $row->policy_document_id] = true;
                endif;
            endforeach;
            if(!empty($duplicates)):
                DB::table(PolicyRole::PIVOT_TABLE)->whereIn('id', $duplicates)->delete();
            endif;
            $ticked = array_keys($ticked);

            /* Both lists hold live policies only (SoftDeletes), which is what
               leaves a tick on an archived policy out of the comparison. */
            $wanted = (!empty($policyIds) ? array_map('intval', PolicyDocument::whereIn('id', $policyIds)->pluck('id')->all()) : []);
            $tickedLive = (!empty($ticked) ? array_map('intval', PolicyDocument::whereIn('id', $ticked)->pluck('id')->all()) : []);

            $remove = array_values(array_diff($tickedLive, $wanted));
            $add = array_values(array_diff($wanted, $ticked));
            if(!empty($remove)):
                $locked->policies()->detach($remove);
            endif;
            if(!empty($add)):
                $locked->policies()->attach($add, ['created_by' => $userId]);
            endif;
        });

        /* Anything loaded before the save is now out of date. */
        $role->unsetRelation('policies')->unsetRelation('activePolicies');
    }

    /**
     * The attempt the member of staff should be looking at: the one already in
     * progress if there is one (same questions, same order — a refresh never
     * redraws), otherwise a fresh random draw.
     *
     * The caller checks canAttempt() first; this re-checks under the lock so
     * two tabs, or a submit racing a start, cannot draw twice or draw after a
     * pass.
     *
     * The draw follows the test's pattern (PolicyLevel::MIX): a fixed share of
     * questions from each level, shuffled together. The attempt records the
     * test's level, and each drawn question records its own.
     *
     * On a timed policy the clock starts here and expires_at is fixed. An
     * attempt whose clock ran out while the page was closed is marked instead
     * of resumed, and a ValidationException ('attempt') says so — a new one is
     * only started when the member of staff asks again.
     */
    public function startOrResumeAttempt(PolicyAssignment $assignment, ?string $ip = null): PolicyAttempt
    {
        $attempt = DB::transaction(function () use ($assignment, $ip) {
            $locked = PolicyAssignment::where('id', $assignment->id)->lockForUpdate()->first();
            if(!$locked):
                throw (new ModelNotFoundException())->setModel(PolicyAssignment::class, [$assignment->id]);
            endif;

            $open = PolicyAttempt::where('policy_assignment_id', $locked->id)
                ->where('status', PolicyAttempt::STATUS_IN_PROGRESS)
                ->orderBy('id', 'DESC')
                ->first();
            if($open && !$open->isPastGrace()):
                return $open;
            endif;
            if($open):
                /* Its clock ran out while the page was closed: mark it now. A
                   fresh attempt is never started in the same breath — the
                   member of staff is told first, and starts again themselves. */
                $this->submitAttempt($open, []);

                return null;
            endif;

            if(!$locked->canAttempt()):
                throw ValidationException::withMessages(['attempt' => 'This test is not available.']);
            endif;

            $policy = $locked->policy;
            $level = $locked->levelKey();
            /* The test's pattern: so many questions from each level, drawn at
               random within the level and then shuffled together. A level
               that cannot fill its share is never topped up from another —
               canAttempt() has already refused the test in that case. */
            $quota = PolicyLevel::quota($level, max(1, (int) $policy->questions_per_attempt));
            $questions = collect();
            foreach($quota as $questionLevel => $count):
                if($count <= 0):
                    continue;
                endif;
                $drawn = PolicyQuestion::where('policy_document_id', $policy->id)
                    ->drawable()
                    ->ofLevel($questionLevel)
                    ->with('options')
                    ->inRandomOrder()
                    ->limit($count)
                    ->get();
                if($drawn->count() < $count):
                    throw ValidationException::withMessages(['attempt' => 'This test is not available.']);
                endif;
                $questions = $questions->concat($drawn);
            endforeach;
            if($questions->isEmpty()):
                throw ValidationException::withMessages(['attempt' => 'This test is not available.']);
            endif;
            $questions = $questions->shuffle()->values();

            $userId = auth()->id();
            $startedAt = Carbon::now();
            $timeLimit = ($policy->isTimed() ? (int) $policy->time_limit_minutes : null);
            $attempt = PolicyAttempt::create([
                'policy_assignment_id' => $locked->id,
                'employee_id' => $locked->employee_id,
                'policy_document_id' => $policy->id,
                'level' => $level,
                'attempt_no' => (int) $locked->attempts_count + 1,
                'status' => PolicyAttempt::STATUS_IN_PROGRESS,
                'started_at' => $startedAt,
                'total_questions' => $questions->count(),
                'pass_mark' => (int) $policy->pass_mark,
                'time_limit_minutes' => $timeLimit,
                'expires_at' => ($timeLimit !== null ? $startedAt->copy()->addMinutes($timeLimit) : null),
                'timed_out' => 0,
                'ip_address' => $ip,
                'created_by' => $userId,
            ]);

            $sort = 0;
            foreach($questions as $question):
                $sort += 1;
                $options = $question->options->shuffle()->values();
                $correct = $question->options->first(function ($option) {
                    return (bool) $option->is_correct;
                });

                PolicyAttemptAnswer::create([
                    'policy_attempt_id' => $attempt->id,
                    'policy_question_id' => $question->id,
                    'sort_order' => $sort,
                    'question_text' => $question->question,
                    'question_level' => $question->level,
                    'option_order' => $options->pluck('id')->map(function ($id) {
                        return (int) $id;
                    })->all(),
                    'options_snapshot' => $options->map(function ($option) {
                        return ['id' => (int) $option->id, 'text' => $option->option_text];
                    })->all(),
                    'correct_option_id' => ($correct ? $correct->id : null),
                    'correct_option_text' => ($correct ? $correct->option_text : null),
                ]);
            endforeach;

            return $attempt;
        });

        if($attempt === null):
            throw ValidationException::withMessages(['attempt' => 'The time for your last attempt ran out, so it has been marked. Start the test again when you are ready.']);
        endif;

        return $attempt->load('answers');
    }

    /**
     * Mark every attempt whose clock ran out without a submission — the page
     * was closed, or the browser never sent the auto-submit. Nothing was
     * answered in time, so each is marked as it stands: timed out, no score.
     *
     * Cheap enough to call at the top of any screen that shows statuses, so an
     * abandoned timed test never lingers as "In progress". Pass an employee id
     * to limit it to that person's attempts. Returns how many were marked.
     */
    public function finaliseExpiredAttempts(?int $employeeId = null): int
    {
        $query = PolicyAttempt::where('status', PolicyAttempt::STATUS_IN_PROGRESS)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now()->subSeconds(PolicyAttempt::GRACE_SECONDS));
        if($employeeId !== null):
            $query->where('employee_id', $employeeId);
        endif;

        $done = 0;
        foreach($query->orderBy('id', 'ASC')->get() as $attempt):
            try {
                $this->submitAttempt($attempt, []);
                $done += 1;
            } catch (\Throwable $ex) {
                Log::error('Policy assessment: could not mark an expired attempt: '.$ex->getMessage(), ['attempt_id' => $attempt->id]);
            }
        endforeach;

        return $done;
    }

    /**
     * The three figures on the HR Portal: how many policy tests are assigned
     * to current staff, how many of them have met the target, and how many
     * were sat without meeting it (whether or not a retake is left). The rest
     * have not been sat yet.
     *
     * Counted the way Overview & Results counts — assignments on policies
     * that still exist, held by active staff — so the two screens agree.
     */
    public function assignmentTotals(): array
    {
        /* A timed test whose clock ran out is marked before anything is counted. */
        $this->finaliseExpiredAttempts();

        $row = DB::table('policy_assignments as pa')
            ->join('policy_documents as pd', function ($join) {
                $join->on('pd.id', '=', 'pa.policy_document_id')->whereNull('pd.deleted_at');
            })
            ->join('employees as e', function ($join) {
                $join->on('e.id', '=', 'pa.employee_id')->whereNull('e.deleted_at');
            })
            ->whereNull('pa.deleted_at')
            ->where('e.status', 1)
            ->selectRaw('COUNT(pa.id) as assigned_count')
            ->selectRaw('SUM(CASE WHEN pa.status = ? THEN 1 ELSE 0 END) as met_count', [PolicyAssignment::STATUS_PASSED])
            ->selectRaw('SUM(CASE WHEN pa.status = ? THEN 1 ELSE 0 END) as not_met_count', [PolicyAssignment::STATUS_FAILED])
            ->first();

        return [
            'assigned' => (int) (isset($row->assigned_count) ? $row->assigned_count : 0),
            'met' => (int) (isset($row->met_count) ? $row->met_count : 0),
            'not_met' => (int) (isset($row->not_met_count) ? $row->not_met_count : 0),
        ];
    }

    /**
     * Mark an attempt. $answers is [answer row id => chosen option id].
     *
     * Every drawn question must be answered with one of the options it was
     * shown with, or nothing is saved. Submitting an attempt that is already
     * marked returns the stored result unchanged, so a double click or a
     * retried request cannot use up a second attempt.
     *
     * Two exceptions to "every question", both marking what is unanswered as
     * wrong: $allowIncomplete, when the member of staff has confirmed on the
     * Review step that they want to hand in an unfinished test; and a timed
     * attempt whose clock is at zero (timed_out). After the grace period
     * nothing sent counts.
     *
     * The assignment's first pass awards the badge for its (employee, policy,
     * level) in the same transaction — unless a live one is already held, e.g.
     * from an earlier assignment HR archived. A fail never awards one.
     */
    public function submitAttempt(PolicyAttempt $attempt, array $answers, bool $allowIncomplete = false): array
    {
        $marked = DB::transaction(function () use ($attempt, $answers, $allowIncomplete) {
            /* Same lock order as startOrResumeAttempt(): assignment, then attempt. */
            $assignment = PolicyAssignment::withTrashed()->where('id', $attempt->policy_assignment_id)->lockForUpdate()->first();
            $locked = PolicyAttempt::where('id', $attempt->id)->lockForUpdate()->first();
            if(!$locked || !$assignment):
                throw (new ModelNotFoundException())->setModel(PolicyAttempt::class, [$attempt->id]);
            endif;

            if($locked->status == PolicyAttempt::STATUS_SUBMITTED):
                return $locked;
            endif;

            $rows = PolicyAttemptAnswer::where('policy_attempt_id', $locked->id)->orderBy('sort_order', 'ASC')->get();
            if($rows->isEmpty()):
                throw ValidationException::withMessages(['answers' => 'Please answer every question before submitting.']);
            endif;

            /* A timed test: once the clock is at zero the submission no longer
               has to be complete — whatever is unanswered is marked wrong. Past
               the grace period nothing sent counts at all: the page submits
               itself at zero, so a later answer was not given in the time. */
            $pastGrace = $locked->isPastGrace();
            $clockAtZero = $locked->acceptsIncomplete();
            /* The member of staff may also choose to hand in an unfinished
               test — the Review step asks them to confirm it first. */
            $allowIncomplete = ($allowIncomplete || $clockAtZero);

            $selections = [];
            $unanswered = 0;
            foreach($rows as $row):
                $chosen = (isset($answers[$row->id]) ? $answers[$row->id] : null);
                $allowed = array_map('intval', (is_array($row->option_order) ? $row->option_order : []));
                $valid = (is_scalar($chosen) && ctype_digit((string) $chosen) && in_array((int) $chosen, $allowed, true));
                if($valid && !$pastGrace):
                    $selections[$row->id] = (int) $chosen;
                elseif($pastGrace || $allowIncomplete):
                    $selections[$row->id] = null;
                    $unanswered += 1;
                else:
                    throw ValidationException::withMessages(['answers' => 'Please answer every question before submitting.']);
                endif;
            endforeach;
            /* "Timed out" means the clock decided it, not the member of staff. */
            $timedOut = ($locked->isTimed() && $unanswered > 0 && ($pastGrace || $clockAtZero));

            $correctCount = 0;
            foreach($rows as $row):
                $chosen = $selections[$row->id];
                $chosenText = null;
                foreach((is_array($row->options_snapshot) ? $row->options_snapshot : []) as $option):
                    if($chosen !== null && isset($option['id']) && (int) $option['id'] === $chosen):
                        $chosenText = (isset($option['text']) ? $option['text'] : null);
                        break;
                    endif;
                endforeach;

                $isCorrect = ($chosen !== null && $row->correct_option_id !== null && (int) $row->correct_option_id === $chosen);
                if($isCorrect):
                    $correctCount += 1;
                endif;

                $row->selected_option_id = $chosen;
                $row->selected_option_text = $chosenText;
                $row->is_correct = $isCorrect;
                $row->save();
            endforeach;

            $total = $rows->count();
            $score = round($correctCount / $total * 100, 2);
            $passed = ($score >= (int) $locked->pass_mark);
            /* An attempt marked after its clock ran out ended when the clock
               did, not whenever somebody next looked at it. */
            $now = ($pastGrace ? $locked->expires_at->copy() : Carbon::now());
            $userId = auth()->id();

            $locked->status = PolicyAttempt::STATUS_SUBMITTED;
            $locked->submitted_at = $now;
            $locked->total_questions = $total;
            $locked->correct_count = $correctCount;
            $locked->score = $score;
            $locked->passed = $passed;
            $locked->timed_out = $timedOut;
            $locked->updated_by = $userId;
            $locked->save();

            /* Still away when the test was handed in (or the clock ran out):
               the exit ends at that moment. */
            foreach(PolicyAttemptEvent::where('policy_attempt_id', $locked->id)->whereNull('returned_at')->get() as $event):
                $this->closeAttemptEvent($locked, $event, $now);
            endforeach;

            $alreadyPassed = ($assignment->status == PolicyAssignment::STATUS_PASSED);
            $assignment->attempts_count = (int) $assignment->attempts_count + 1;
            $assignment->last_score = $score;
            $assignment->best_score = ($assignment->best_score !== null ? max((float) $assignment->best_score, $score) : $score);
            $assignment->last_attempt_at = $now;
            $assignment->status = ($passed || $alreadyPassed ? PolicyAssignment::STATUS_PASSED : PolicyAssignment::STATUS_FAILED);
            if($passed && $assignment->passed_at === null):
                $assignment->passed_at = $now;
            endif;
            $assignment->updated_by = $userId;
            $assignment->save();

            if($passed && !$alreadyPassed):
                $this->awardBadge($assignment, $locked, $now, $userId);
            endif;

            return $locked;
        });

        return $this->resultPayload($marked);
    }

    /**
     * The member of staff has left the test: switched tab, minimised the
     * browser ('tab') or clicked outside the window ('window'). Opens an exit
     * stamped with the server's time. Leaving again before coming back does
     * not open a second one — the first is still running — though a 'window'
     * exit that turns into a tab switch is recorded as the stronger 'tab'.
     *
     * Nothing is logged once the attempt is marked, or past the per-attempt
     * cap. Returns the open exit, or NULL when nothing was logged.
     */
    public function logAttemptLeave(PolicyAttempt $attempt, string $type, ?int $questionNo = null): ?PolicyAttemptEvent
    {
        $type = ($type === PolicyAttemptEvent::TYPE_TAB ? PolicyAttemptEvent::TYPE_TAB : PolicyAttemptEvent::TYPE_WINDOW);
        $questionNo = ($questionNo !== null && $questionNo > 0 && $questionNo <= 65535 ? $questionNo : null);

        return DB::transaction(function () use ($attempt, $type, $questionNo) {
            $locked = PolicyAttempt::where('id', $attempt->id)->lockForUpdate()->first();
            if(!$locked || $locked->status != PolicyAttempt::STATUS_IN_PROGRESS):
                return null;
            endif;

            $open = PolicyAttemptEvent::where('policy_attempt_id', $locked->id)->whereNull('returned_at')->orderBy('id', 'DESC')->first();
            if($open):
                if($type === PolicyAttemptEvent::TYPE_TAB && $open->type !== PolicyAttemptEvent::TYPE_TAB):
                    $open->type = PolicyAttemptEvent::TYPE_TAB;
                    $open->save();
                endif;

                return $open;
            endif;

            if(PolicyAttemptEvent::where('policy_attempt_id', $locked->id)->count() >= self::MAX_EVENTS_PER_ATTEMPT):
                return null;
            endif;

            return PolicyAttemptEvent::create([
                'policy_attempt_id' => $locked->id,
                'type' => $type,
                'question_no' => $questionNo,
                'left_at' => Carbon::now(),
            ]);
        });
    }

    /**
     * They are back on the test: close the open exit at the server's time.
     *
     * If no exit is open — the "left" signal never arrived, as can happen
     * when a tab is frozen — the exit is rebuilt from the page's own
     * stopwatch ($awayMs), never longer than the attempt has been running.
     * Returns the closed exit, or NULL when there was nothing to close.
     */
    public function logAttemptReturn(PolicyAttempt $attempt, ?int $awayMs = null, ?string $type = null, ?int $questionNo = null): ?PolicyAttemptEvent
    {
        $type = ($type === PolicyAttemptEvent::TYPE_TAB ? PolicyAttemptEvent::TYPE_TAB : PolicyAttemptEvent::TYPE_WINDOW);
        $questionNo = ($questionNo !== null && $questionNo > 0 && $questionNo <= 65535 ? $questionNo : null);

        return DB::transaction(function () use ($attempt, $awayMs, $type, $questionNo) {
            $locked = PolicyAttempt::where('id', $attempt->id)->lockForUpdate()->first();
            if(!$locked || $locked->status != PolicyAttempt::STATUS_IN_PROGRESS):
                return null;
            endif;

            $now = Carbon::now();
            $open = PolicyAttemptEvent::where('policy_attempt_id', $locked->id)->whereNull('returned_at')->orderBy('id', 'DESC')->first();
            if(!$open):
                if($awayMs === null || $awayMs <= 0):
                    return null;
                endif;
                if(PolicyAttemptEvent::where('policy_attempt_id', $locked->id)->count() >= self::MAX_EVENTS_PER_ATTEMPT):
                    return null;
                endif;

                $running = max(1, (int) $locked->started_at->diffInSeconds($now));
                $seconds = max(1, min((int) round($awayMs / 1000), $running));
                $open = PolicyAttemptEvent::create([
                    'policy_attempt_id' => $locked->id,
                    'type' => $type,
                    'question_no' => $questionNo,
                    'left_at' => $now->copy()->subSeconds($seconds),
                ]);
            endif;

            return $this->closeAttemptEvent($locked, $open, $now);
        });
    }

    /**
     * Close an exit at $at and add it to the attempt's running totals. An
     * exit always counts as at least one second.
     */
    protected function closeAttemptEvent(PolicyAttempt $attempt, PolicyAttemptEvent $event, Carbon $at): PolicyAttemptEvent
    {
        $seconds = ($at->gt($event->left_at) ? max(1, (int) $event->left_at->diffInSeconds($at)) : 1);

        $event->returned_at = $at;
        $event->seconds = $seconds;
        $event->save();

        PolicyAttempt::where('id', $attempt->id)->update([
            'tab_exits' => DB::raw('tab_exits + 1'),
            'away_seconds' => DB::raw('away_seconds + '.(int) $seconds),
        ]);

        return $event;
    }

    /**
     * An attempt's tab activity as the result page and HR's review show it:
     * ['exits' => n, 'away_seconds' => n, 'events' => [['at' => 'H:i:s',
     * 'question_no' => n|null, 'seconds' => n, 'type' => 'tab'|'window',
     * 'type_label' => '…', 'open' => bool], …]] in the order they happened.
     * An exit still open (the test is in progress and they are away) is
     * listed with 'open' => true and no seconds, and is not counted.
     */
    public function tabActivity(PolicyAttempt $attempt): array
    {
        $rows = PolicyAttemptEvent::where('policy_attempt_id', $attempt->id)->orderBy('left_at', 'ASC')->orderBy('id', 'ASC')->get();

        $events = [];
        $exits = 0;
        $away = 0;
        foreach($rows as $row):
            $closed = ($row->returned_at !== null);
            if($closed):
                $exits += 1;
                $away += (int) $row->seconds;
            endif;
            $events[] = [
                'at' => $row->left_at->format('H:i:s'),
                'question_no' => ($row->question_no !== null ? (int) $row->question_no : null),
                'seconds' => ($closed ? (int) $row->seconds : null),
                'type' => $row->type,
                'type_label' => $row->type_label,
                'open' => !$closed,
            ];
        endforeach;

        return ['exits' => $exits, 'away_seconds' => $away, 'events' => $events];
    }

    /**
     * What a member of staff is told about a marked attempt. Deliberately no
     * per-question detail: telling someone which answers were wrong would let
     * them retake the test without reading the policy.
     *
     * Always carries the level of the test, whether the clock ran out
     * (timed_out), the time limit it ran under and how many questions were
     * answered. A passed attempt also
     * carries 'badge' — the live badge now held for this policy at this level
     * (see badgePayload()), or NULL if HR has since revoked it. A failed one
     * has no 'badge' key at all.
     */
    public function resultPayload(PolicyAttempt $attempt): array
    {
        $assignment = PolicyAssignment::withTrashed()->with('policy.category')->find($attempt->policy_assignment_id);
        $attemptsLeft = ($assignment ? $assignment->attemptsLeft() : 0);
        $canRetake = (
            $assignment
            && !$assignment->trashed()
            && $attempt->status == PolicyAttempt::STATUS_SUBMITTED
            && !$attempt->passed
            && $assignment->canAttempt()
        );

        $level = (PolicyLevel::isValid($attempt->level) ? $attempt->level : ($assignment ? $assignment->levelKey() : PolicyLevel::BEGINNER));

        $payload = [
            'score' => (float) $attempt->score,
            'passed' => (bool) $attempt->passed,
            'correct_count' => (int) $attempt->correct_count,
            'total_questions' => (int) $attempt->total_questions,
            'pass_mark' => (int) $attempt->pass_mark,
            'attempt_no' => (int) $attempt->attempt_no,
            'attempts_left' => $attemptsLeft,
            'can_retake' => (bool) $canRetake,
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'timed_out' => (bool) $attempt->timed_out,
            'time_limit_minutes' => ($attempt->time_limit_minutes !== null ? (int) $attempt->time_limit_minutes : null),
            'answered_count' => (int) PolicyAttemptAnswer::where('policy_attempt_id', $attempt->id)->whereNotNull('selected_option_id')->count(),
            'tab_exits' => (int) PolicyAttemptEvent::where('policy_attempt_id', $attempt->id)->whereNotNull('returned_at')->count(),
            'away_seconds' => (int) PolicyAttemptEvent::where('policy_attempt_id', $attempt->id)->whereNotNull('returned_at')->sum('seconds'),
        ];

        if($attempt->status == PolicyAttempt::STATUS_SUBMITTED && (bool) $attempt->passed):
            $badge = PolicyBadge::with('policy')
                ->where('employee_id', $attempt->employee_id)
                ->where('policy_document_id', $attempt->policy_document_id)
                ->where('level', $level)
                ->orderBy('id', 'DESC')
                ->first();
            $payload['badge'] = ($badge ? $this->badgePayload($badge) : null);
        endif;

        return $payload;
    }

    /**
     * A badge as the staff pages show it — nothing about answers:
     * ['level', 'level_label', 'badge_name', 'policy_title', 'awarded_at' (d M Y)].
     */
    public function badgePayload(PolicyBadge $badge): array
    {
        $level = (string) $badge->level;
        $policy = $badge->policy;

        return [
            'level' => $level,
            'level_label' => PolicyLevel::label($level),
            'badge_name' => PolicyLevel::badgeName($level),
            'policy_title' => ($policy ? (string) $policy->title : ''),
            'awarded_at' => ($badge->awarded_at ? $badge->awarded_at->format('d M Y') : ''),
        ];
    }

    /**
     * HR takes a badge back: it is soft-deleted, with revoked_by recording
     * who. Revoking one that is already revoked does nothing.
     */
    public function revokeBadge(PolicyBadge $b): void
    {
        if($b->trashed()):
            return;
        endif;

        $userId = auth()->id();
        DB::transaction(function () use ($b, $userId) {
            $b->revoked_by = $userId;
            $b->updated_by = $userId;
            $b->save();
            $b->delete();
        });
    }

    /**
     * Undo a revoke. Refused with a ValidationException on 'badge' (a 422)
     * when the member of staff already holds another live badge for the same
     * policy and level — at most one live badge each. Restoring a badge that
     * is not revoked returns it unchanged.
     *
     * @throws ModelNotFoundException when there is no badge with that id.
     */
    public function restoreBadge(int $id): PolicyBadge
    {
        $userId = auth()->id();

        return DB::transaction(function () use ($id, $userId) {
            $badge = PolicyBadge::withTrashed()->where('id', $id)->lockForUpdate()->first();
            if(!$badge):
                throw (new ModelNotFoundException())->setModel(PolicyBadge::class, [$id]);
            endif;
            if(!$badge->trashed()):
                return $badge;
            endif;

            $duplicate = PolicyBadge::where('employee_id', $badge->employee_id)
                ->where('policy_document_id', $badge->policy_document_id)
                ->where('level', $badge->level)
                ->where('id', '!=', $badge->id)
                ->lockForUpdate()
                ->exists();
            if($duplicate):
                throw ValidationException::withMessages(['badge' => 'This member of staff already holds the '.PolicyLevel::label($badge->level).' badge for this policy, so this one cannot be restored.']);
            endif;

            $badge->revoked_by = null;
            $badge->updated_by = $userId;
            $badge->restore();

            return $badge;
        });
    }

    /**
     * Called inside submitAttempt()'s transaction with the assignment row
     * locked. The locking read on the badge's (employee, policy, level) range
     * stops a concurrent pass on another assignment adding a second badge.
     */
    protected function awardBadge(PolicyAssignment $assignment, PolicyAttempt $attempt, Carbon $now, ?int $userId): ?PolicyBadge
    {
        $level = $assignment->levelKey();
        $held = PolicyBadge::where('employee_id', $assignment->employee_id)
            ->where('policy_document_id', $assignment->policy_document_id)
            ->where('level', $level)
            ->lockForUpdate()
            ->exists();
        if($held):
            return null;
        endif;

        return PolicyBadge::create([
            'employee_id' => $assignment->employee_id,
            'policy_document_id' => $assignment->policy_document_id,
            'level' => $level,
            'policy_assignment_id' => $assignment->id,
            'policy_attempt_id' => $attempt->id,
            'score' => $attempt->score,
            'awarded_at' => $now,
            'created_by' => $userId,
        ]);
    }

    /**
     * One more attempt on top of the policy's limit. No effect when attempts
     * are unlimited.
     *
     * If HR added or lowered the limit after the member of staff had already
     * used more attempts than it allows, a plain +1 would still leave them
     * with none, so the grant is topped up to leave exactly one attempt free.
     */
    public function grantExtraAttempt(PolicyAssignment $a): void
    {
        $extra = (int) $a->extra_attempts + 1;
        $policy = $a->policy;
        if($policy && $policy->max_attempts !== null):
            $extra = max($extra, (int) $a->attempts_count - (int) $policy->max_attempts + 1);
        endif;

        $a->extra_attempts = $extra;
        $a->updated_by = auth()->id();
        $a->save();
    }

    /**
     * Headline numbers for one member of staff, over their live assignments
     * on policies that have not been deleted.
     *
     * passed + locked + in_progress + failed + pending always equals assigned
     * (failed/pending exclude anything in progress or locked). overdue is a
     * separate flag that cuts across them: every unpassed assignment past its
     * due date, whatever else is true of it.
     *
     * badges counts the live (not revoked) badges held on policies that have
     * not been deleted — see badgeCounts().
     */
    public function employeeSummary(int $employeeId): array
    {
        $assignments = PolicyAssignment::with(['policy', 'openAttempt'])
            ->where('employee_id', $employeeId)
            ->whereHas('policy', function ($query) {
                $query->whereNull('policy_documents.deleted_at');
            })
            ->get();

        $summary = [
            'assigned' => $assignments->count(),
            'passed' => 0,
            'failed' => 0,
            'pending' => 0,
            'overdue' => 0,
            'in_progress' => 0,
            'locked' => 0,
            'completion_percent' => 0,
        ];

        foreach($assignments as $assignment):
            $display = $assignment->display_status;
            if($display == 'passed'):
                $summary['passed'] += 1;
            elseif($display == 'locked'):
                $summary['locked'] += 1;
            elseif($display == 'in_progress'):
                $summary['in_progress'] += 1;
            elseif($assignment->status == PolicyAssignment::STATUS_FAILED):
                $summary['failed'] += 1;
            else:
                $summary['pending'] += 1;
            endif;

            if($assignment->isOverdue()):
                $summary['overdue'] += 1;
            endif;
        endforeach;

        $summary['completion_percent'] = ($summary['assigned'] > 0 ? (int) round($summary['passed'] / $summary['assigned'] * 100) : 0);
        $summary['badges'] = $this->badgeCounts($employeeId);

        return $summary;
    }

    /**
     * Live (not revoked) badges on policies that have not been deleted:
     * ['beginner' => n, 'intermediate' => n, 'expert' => n, 'total' => n].
     */
    public function badgeCounts(int $employeeId): array
    {
        $grouped = PolicyBadge::where('employee_id', $employeeId)
            ->whereHas('policy', function ($query) {
                $query->whereNull('policy_documents.deleted_at');
            })
            ->selectRaw('policy_badges.level as lvl, COUNT(*) as aggregate')
            ->groupBy('policy_badges.level')
            ->pluck('aggregate', 'lvl')
            ->all();

        $counts = [];
        $total = 0;
        foreach(PolicyLevel::all() as $level):
            $counts[$level] = (isset($grouped[$level]) ? (int) $grouped[$level] : 0);
            $total += $counts[$level];
        endforeach;
        $counts['total'] = $total;

        return $counts;
    }

    /**
     * SMTP settings for HR's policy emails: the default account, else the
     * first one on file. NULL when there is none — callers then skip the
     * email. No credentials are ever hard-coded here.
     */
    public function mailConfiguration(): ?array
    {
        $smtp = ComonSmtp::where('is_default', 1)->first();
        if(!$smtp):
            $smtp = ComonSmtp::first();
        endif;
        if(!$smtp || empty($smtp->smtp_host)):
            return null;
        endif;

        return [
            'smtp_host' => $smtp->smtp_host,
            'smtp_port' => (!empty($smtp->smtp_port) ? $smtp->smtp_port : '587'),
            'smtp_username' => $smtp->smtp_user,
            'smtp_password' => $smtp->smtp_pass,
            'smtp_encryption' => (!empty($smtp->smtp_encryption) ? $smtp->smtp_encryption : 'tls'),
            'from_email' => self::FROM_EMAIL,
            'from_name' => self::FROM_NAME,
        ];
    }

    /** Work address first, personal address as the fallback. */
    public function employeeEmail(Employee $e): ?string
    {
        $employment = $e->employment;
        if(isset($employment->email) && trim((string) $employment->email) !== ''):
            return trim($employment->email);
        endif;
        if(isset($e->email) && trim((string) $e->email) !== ''):
            return trim($e->email);
        endif;

        return null;
    }

    public function sendAssignmentEmail(Employee $e, Collection $assignments): bool
    {
        $intro = 'You have been asked to read the college '.($assignments->count() == 1 ? 'policy' : 'policies').' below and complete a short test on each one. '
            .'Open each policy from My HR, read it, then take the test at the level shown. You need to reach the target score to complete it.';

        return $this->sendPolicyEmail($e, $assignments, 'Policy assessments assigned to you', $intro, false);
    }

    public function sendReminderEmail(Employee $e, Collection $assignments): bool
    {
        $intro = 'This is a reminder that the policy '.($assignments->count() == 1 ? 'test below is' : 'tests below are').' due soon or overdue. '
            .'Please read each policy and complete its test from My HR.';

        return $this->sendPolicyEmail($e, $assignments, 'Reminder: policy assessments due', $intro, true);
    }

    protected function sendPolicyEmail(Employee $e, Collection $assignments, string $subject, string $intro, bool $showStatus): bool
    {
        try {
            if($assignments->isEmpty()):
                return false;
            endif;

            $configuration = $this->mailConfiguration();
            if($configuration === null):
                Log::warning('Policy assessment email not sent: no SMTP configuration found.', ['employee_id' => $e->id]);
                return false;
            endif;

            $to = $this->employeeEmail($e);
            if($to === null):
                Log::warning('Policy assessment email not sent: employee has no email address.', ['employee_id' => $e->id]);
                return false;
            endif;

            $html = $this->emailBody($e, $assignments, $intro, $showStatus);
            UserMailerJob::dispatch($configuration, [$to], new CommunicationSendMail($subject, $html, []));

            return true;
        } catch (\Throwable $ex) {
            Log::error('Policy assessment email failed: '.$ex->getMessage(), ['employee_id' => $e->id]);
            return false;
        }
    }

    /**
     * The inner HTML for CommunicationSendMail's branded template, which adds
     * the logo header and the disclaimer footer. Inline styles only — this is
     * read in mail clients.
     */
    protected function emailBody(Employee $e, Collection $assignments, string $intro, bool $showStatus): string
    {
        $link = (Route::has('user.account.policy') ? route('user.account.policy') : url('my-account/policy-assessments'));
        $firstName = $this->displayFirstName($e);
        $today = Carbon::today();

        $cell = 'padding:8px 10px; border:1px solid #e2e8f0; font-size:14px; line-height:1.4; color:#1f2937; text-align:left; vertical-align:top;';
        $head = 'padding:8px 10px; border:1px solid #e2e8f0; font-size:13px; line-height:1.4; color:#334155; background-color:#f1f5f9; text-align:left;';

        $rows = '';
        foreach($assignments as $assignment):
            $policy = $assignment->policy;
            $title = ($policy ? $policy->title : 'Policy');
            $category = (isset($policy->category->name) ? $policy->category->name : '');
            $level = $assignment->levelKey();
            $colours = PolicyLevel::colours($level);
            $levelCell = '<span style="display:inline-block; padding:2px 9px; border-radius:10px; background-color:'.$colours['soft'].'; color:'.$colours['ink'].'; font-size:12px; font-weight:bold; white-space:nowrap;">'.e(PolicyLevel::label($level)).'</span>';
            $due = ($assignment->due_date ? $assignment->due_date->format('d M Y') : 'No due date');
            if($showStatus && $assignment->due_date && $assignment->due_date->lt($today)):
                $due = '<span style="color:#b91c1c; font-weight:bold;">'.e($due).' (overdue)</span>';
            else:
                $due = e($due);
            endif;

            $rows .= '<tr>'
                .'<td style="'.$cell.'">'.e($title).'</td>'
                .'<td style="'.$cell.'">'.$levelCell.'</td>'
                .'<td style="'.$cell.'">'.e($category).'</td>'
                .'<td style="'.$cell.' white-space:nowrap;">'.$due.'</td>'
                .'</tr>';
        endforeach;

        $html = '';
        $html .= '<p style="margin:0 0 14px 0; font-size:15px; line-height:1.6; color:#1f2937; text-align:left;">Dear '.e($firstName).',</p>';
        $html .= '<p style="margin:0 0 18px 0; font-size:15px; line-height:1.6; color:#1f2937; text-align:left;">'.e($intro).'</p>';
        $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0 0 22px 0;">'
            .'<tr><th style="'.$head.'">Policy</th><th style="'.$head.'">Level</th><th style="'.$head.'">Category</th><th style="'.$head.'">Due date</th></tr>'
            .$rows
            .'</table>';
        $html .= '<p style="margin:0 0 22px 0; text-align:center;">'
            .'<a href="'.e($link).'" style="display:inline-block; background-color:#164e63; color:#ffffff; padding:11px 22px; border-radius:6px; font-size:15px; font-weight:bold; text-decoration:none;">Go to my policy assessments</a>'
            .'</p>';
        $html .= '<p style="margin:0 0 6px 0; font-size:14px; line-height:1.6; color:#475569; text-align:left;">If the button does not work, sign in to DataFuture and open My HR &rsaquo; Policy Assessments.</p>';
        $html .= '<p style="margin:18px 0 0 0; font-size:15px; line-height:1.6; color:#1f2937; text-align:left;">Kind regards,<br>HR Team</p>';

        return $html;
    }

    /** HR records often store names in capitals; an email should not shout. */
    protected function displayFirstName(Employee $e): string
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) $e->first_name));
        if($name === ''):
            return 'colleague';
        endif;
        if(preg_match('/[a-z]/', $name)):
            return $name;
        endif;

        return mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * [policy id => role id] for assign(): each policy the given roles
     * currently cover, against the first role — in the order given — that
     * covers it. Only live, active roles count, and only their live policies
     * (PolicyRole::activePolicies()).
     */
    protected function rolePolicyMap(array $roleIds): array
    {
        if(empty($roleIds)):
            return [];
        endif;

        $roles = PolicyRole::active()->whereIn('policy_roles.id', $roleIds)->with('activePolicies')->get()->keyBy('id');

        $map = [];
        foreach($roleIds as $roleId):
            if(!isset($roles[$roleId])):
                continue;
            endif;
            foreach($roles[$roleId]->activePolicies as $policy):
                if(!isset($map[(int) $policy->id])):
                    $map[(int) $policy->id] = (int) $roleId;
                endif;
            endforeach;
        endforeach;

        return $map;
    }

    protected function cleanIds(array $ids): array
    {
        $clean = [];
        foreach($ids as $id):
            if(is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0):
                $clean[] = (int) $id;
            endif;
        endforeach;

        return array_values(array_unique($clean));
    }

    /** Accepts Y-m-d or d-m-Y; returns Y-m-d or NULL. */
    protected function normaliseDate(?string $date): ?string
    {
        if($date === null || trim($date) === ''):
            return null;
        endif;

        $date = trim($date);
        foreach(['Y-m-d', 'd-m-Y', 'd/m/Y'] as $format):
            $parsed = \DateTime::createFromFormat('!'.$format, $date);
            if($parsed && $parsed->format($format) === $date):
                return $parsed->format('Y-m-d');
            endif;
        endforeach;

        throw ValidationException::withMessages(['due_date' => 'Please enter a valid due date.']);
    }
}
