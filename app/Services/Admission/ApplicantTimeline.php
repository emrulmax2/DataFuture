<?php

namespace App\Services\Admission;

use App\Models\Applicant;
use App\Support\Avatar;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Everything that happened on one application, oldest first, for the
 * admission portal's Time line tab.
 *
 * Nothing here writes: the events are read back from the tables every other
 * screen already records into (status archive, task logs, documents, notes,
 * emails / SMS / letters, admissions notifications, the e-signature audit,
 * DocuSeal submissions, Task Automation runs, interviews, the student
 * conversion batches and the staff view sessions). Bursts — ten uploads by one
 * person in a few minutes — are folded into one event with its items, the way
 * the prototype shows them.
 *
 * Actors: created_by on the admission tables is a users.id (user 1 is the
 * "System" account the queue, webhooks and rules run as); the applicant and
 * the agent are recognised from the applicant row, never from a created_by,
 * because the applicant-side tables store applicant / agent user ids in that
 * same column.
 */
class ApplicantTimeline
{
    public const CATEGORIES = [
        'status'  => 'Status',
        'task'    => 'Tasks',
        'doc'     => 'Documents',
        'comms'   => 'Communication',
        'note'    => 'Notes',
        'esign'   => 'E-signature',
        'details' => 'Record edits',
        'views'   => 'Views',
        'system'  => 'System',
    ];

    /** Same-timestamp order: a status change reads before what it triggered. */
    private const CAT_ORDER = ['status' => 0, 'system' => 1, 'task' => 2, 'doc' => 3, 'note' => 4, 'comms' => 5, 'esign' => 6, 'details' => 7, 'views' => 8];

    /** Minutes between two actions by one person that still count as one burst. */
    private const BURST_MINUTES = 5;

    /** A gap between two record visits longer than this starts a new visit. */
    private const VISIT_GAP_MINUTES = 30;

    private const SYSTEM_USER = 1;

    private const SECTIONS = [
        'details' => 'Details', 'process' => 'Processes', 'uploads' => 'Uploaded files', 'notes' => 'Notes',
        'communication' => 'Communication', 'esign' => 'E-signature', 'esign_request' => 'E-signature request',
        'log' => 'Conversion log', 'timeline' => 'Time line', 'other' => 'Other pages',
    ];

    private Applicant $a;
    private array $events = [];
    private int $seq = 0;
    /** users.id => true, resolved to names / job titles in one query at the end. */
    private array $staffIds = [];
    private ?Carbon $submittedAt = null;
    private bool $submittedTimeKnown = false;
    /** applicant_documents ids already shown with a note, letter, email or DocuSeal event. */
    private array $shownDocIds = [];
    private array $statusNames = [];

    public function __construct(Applicant $applicant)
    {
        $this->a = $applicant;
    }

    public static function for(Applicant $applicant): array
    {
        return (new self($applicant))->build();
    }

    public function build(): array
    {
        $this->statusNames = DB::table('statuses')->where('type', 'Applicant')->pluck('name', 'id')
            ->map(fn ($n) => $n === 'Awating Decision' ? 'Awaiting Decision' : $n)->all();

        $this->resolveSubmission();
        $this->collectReferencedDocs();

        $this->applicationEvents();
        $this->statusEvents();
        $this->recordEdits();
        $this->adminRecordEvents();
        $this->taskEvents();
        $this->interviewEvents();
        $this->noteEvents();
        $this->communicationEvents();
        $this->documentEvents();
        $this->esignEvents();
        $this->docusealEvents();
        $this->conversionEvents();
        $this->viewEvents();

        usort($this->events, function ($x, $y) {
            return [$x['at'], self::CAT_ORDER[$x['cat']] ?? 9, $x['seq']] <=> [$y['at'], self::CAT_ORDER[$y['cat']] ?? 9, $y['seq']];
        });
        $events = $this->mergeBursts($this->events);

        $actors = $this->actors($events);
        foreach ($events as &$e) {
            if ($e['with'] && isset($actors['u:'.$e['with']])) {
                $e['detail'] = trim($e['detail'].' · for '.$actors['u:'.$e['with']]['name'], ' ·');
            }
            $e['search'] = Str::lower(trim($e['title'].' '.$e['detail'].' '.($actors[$e['who']]['name'] ?? '').' '.implode(' ', array_map(fn ($i) => $i['title'].' '.$i['detail'], $e['items']))));
        }
        unset($e);

        return [
            'events'     => $events,
            'days'       => $this->days($events),
            'actors'     => $actors,
            'people'     => $this->people($events, $actors),
            'counts'     => $this->counts($events),
            'categories' => self::CATEGORIES,
            'summary'    => $this->summary($events),
            'outcome'    => $this->outcome(),
        ];
    }

    /* ------------------------------------------------------------------ sources */

    /**
     * submission_date is a date; the moment is the first thing the submission
     * set off that day (task rules, generated tasks, the e-signature request).
     */
    private function resolveSubmission(): void
    {
        if (empty($this->a->submission_date)) {
            return;
        }
        $day = Carbon::parse($this->a->submission_date)->toDateString();
        $id = $this->a->id;
        $candidates = array_filter([
            DB::table('task_assignment_runs')->where('applicant_id', $id)->whereNull('student_id')->where('trigger', 'application_submitted')->whereDate('created_at', $day)->min('created_at'),
            // only tasks the submission generated (system / rules), not ones staff added that day
            DB::table('applicant_tasks')->where('applicant_id', $id)->whereDate('created_at', $day)
                ->where(fn ($q) => $q->whereNull('created_by')->orWhere('created_by', self::SYSTEM_USER)->orWhere('assigned_via', 'auto'))->min('created_at'),
            DB::table('applicant_e_signature_events')->where('applicant_id', $id)->where('event_type', 'sign_request_created')->whereDate('created_at', $day)->min('created_at'),
        ]);
        $created = $this->a->created_at ? Carbon::parse($this->a->created_at) : null;
        if ($candidates) {
            $this->submittedAt = Carbon::parse(min($candidates));
            $this->submittedTimeKnown = true;
        } elseif ($created && $created->toDateString() === $day) {
            $this->submittedAt = $created->copy()->addSecond();
        } else {
            $this->submittedAt = Carbon::parse($day)->endOfDay()->subMinute();
        }
        if ($created && $this->submittedAt->lt($created)) {
            $this->submittedAt = $created->copy()->addSecond();
        }
    }

    private function collectReferencedDocs(): void
    {
        $id = $this->a->id;
        $ids = array_merge(
            DB::table('applicant_notes')->where('applicant_id', $id)->whereNotNull('applicant_document_id')->pluck('applicant_document_id')->all(),
            DB::table('applicant_letters')->where('applicant_id', $id)->whereNotNull('applicant_document_id')->pluck('applicant_document_id')->all(),
            DB::table('applicant_emails_attachments')->join('applicant_emails', 'applicant_emails.id', '=', 'applicant_emails_attachments.applicant_email_id')
                ->where('applicant_emails.applicant_id', $id)->pluck('applicant_emails_attachments.applicant_document_id')->all(),
            DB::table('applicant_docuseal_submissions')->where('applicant_id', $id)->pluck('offer_document_id')->all(),
            DB::table('applicant_docuseal_submissions')->where('applicant_id', $id)->pluck('audit_document_id')->all(),
        );
        $this->shownDocIds = array_fill_keys(array_filter(array_map('intval', $ids)), true);
    }

    private function applicationEvents(): void
    {
        $a = $this->a;
        $by = !empty($a->agent_user_id) ? 'agent' : 'applicant';
        $course = $this->courseLine();

        // The applicant's account, when it was opened for this application.
        $user = $a->applicant_user_id ? DB::table('applicant_users')->where('id', $a->applicant_user_id)->first() : null;
        $started = $a->created_at ? Carbon::parse($a->created_at) : null;
        if ($user && $started) {
            $near = fn ($v) => $v && Carbon::parse($v)->gte($started->copy()->subDays(3));
            if ($near($user->created_at) && Carbon::parse($user->created_at)->lte($started)) {
                $this->add($user->created_at, 'details', 'Applicant account created', (string) $user->email, $by);
            }
            if ($near($user->email_verified_at)) {
                $this->add($user->email_verified_at, 'details', 'Email address verified', (string) $user->email, 'applicant');
            }
        }

        if ($started) {
            $this->add($started, 'status', 'Application started', $by === 'agent' ? 'Draft created by the agent' : 'Draft created in the applicant portal', $by);
        }
        if ($this->submittedAt) {
            $this->add($this->submittedAt, 'status', 'Application submitted', trim('Ref '.($a->application_no ?: '—').($course !== '' ? ' · '.$course : '')), $by, [
                'milestone' => true,
                'untimed'   => !$this->submittedTimeKnown,
            ]);
        }
    }

    private function statusEvents(): void
    {
        $id = $this->a->id;
        $rows = DB::table('applicant_archives')->where('applicant_id', $id)->where('table', 'applicants')
            ->whereIn('field_name', ['status_id', 'application_rejected_reason_id'])->orderBy('id')->get();
        $reasons = DB::table('application_rejected_reasons')->pluck('name', 'id')->all();
        $reasonRows = $rows->where('field_name', 'application_rejected_reason_id');

        // field_value is not always the previous status (several writers read it after saving),
        // so "from" follows the chain of new values.
        $current = null;
        foreach ($rows->where('field_name', 'status_id') as $r) {
            $to = (int) $r->field_new_value;
            $from = $current ?? (int) $r->field_value;
            $current = $to;
            $toName = $this->statusName($to);
            $at = Carbon::parse($r->created_at);

            $detail = '';
            $reason = $reasonRows->first(fn ($x) => abs(Carbon::parse($x->created_at)->diffInSeconds($at, false)) <= 5 && !empty($x->field_new_value));
            if ($to === 8 && $reason) {
                $detail = 'Reason: '.($reasons[(int) $reason->field_new_value] ?? $reason->field_new_value);
            }
            if ($from === $to) {
                $title = 'Status set to '.$toName.' again';
                $detail = $detail ?: ($to === 6 ? 'The offer was placed again' : 'The status did not change');
            } else {
                $title = $this->statusName($from).' → '.$toName;
            }
            $this->add($at, 'status', $title, $detail, $this->staff($r->created_by), ['milestone' => true, 'tone' => in_array($to, [8, 9, 46], true) ? 'bad' : null]);
        }
    }

    /** Field edits staff made on the record (personal details, contact, course, …), one event per save. */
    private function recordEdits(): void
    {
        $id = $this->a->id;
        $rows = DB::table('applicant_archives')->where('applicant_id', $id)
            ->whereNotIn('field_name', ['status_id', 'application_rejected_reason_id', 'updated_by', 'created_by'])
            ->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return;
        }
        $lookups = new ArchiveValues();

        foreach ($rows->groupBy(fn ($r) => $r->created_at.'|'.$r->created_by.'|'.$r->table) as $group) {
            $first = $group->first();
            if ($first->table === 'applicants' && $first->field_name === 'photo' && $group->count() === 1) {
                $this->add($first->created_at, 'details', 'Profile photo '.(empty($first->field_value) ? 'added' : 'replaced'), '', $this->staff($first->created_by));
                continue;
            }
            $items = [];
            foreach ($group as $r) {
                if ((string) $r->field_value === (string) $r->field_new_value) {
                    continue;
                }
                [$label, $old, $new] = $lookups->describe($r->table, $r->field_name, $r->field_value, $r->field_new_value);
                $items[] = ['time' => null, 'title' => $label, 'detail' => $old.' → '.$new];
            }
            if (!$items) {
                continue;
            }
            $who = $this->staff($first->created_by);
            if ($first->table === 'applicant_users' && (int) $first->created_by === (int) $id) {
                $who = 'applicant'; // the applicant confirmed a new login email from the link we sent
            }
            $title = ArchiveValues::TABLE_TITLES[$first->table] ?? 'Record updated';
            $this->add($first->created_at, 'details', $title, count($items) === 1 ? $items[0]['title'].': '.$items[0]['detail'] : count($items).' fields changed', $who, [
                'items' => count($items) > 1 ? $items : [],
            ]);
        }
    }

    /** Admission-only records staff keep on the application: proof of ID, fee eligibility, quals, jobs, login email. */
    private function adminRecordEvents(): void
    {
        $id = $this->a->id;
        // Quals and jobs saved before staff picked the record up are the applicant's own form data.
        // When the submission moment is only a guess, wait for the first staff action instead.
        $after = null;
        if ($this->submittedAt && $this->submittedTimeKnown) {
            $after = $this->submittedAt->copy()->addMinute();
        } elseif ($this->submittedAt) {
            $first = array_filter([
                DB::table('applicant_archives')->where('applicant_id', $id)->min('created_at'),
                DB::table('applicant_documents')->where('applicant_id', $id)->min('created_at'),
                DB::table('applicant_tasks')->where('applicant_id', $id)->min('created_at'),
            ]);
            $after = $first ? Carbon::parse(min($first)) : null;
        }

        $proof = DB::table('applicant_proof_of_ids')->where('applicant_id', $id)->whereNull('deleted_at')->orderByDesc('id')->first();
        if ($proof && !empty($proof->updated_by) && $proof->updated_at) {
            $detail = implode(' · ', array_filter([
                $proof->proof_type ? Str::headline((string) $proof->proof_type) : null,
                $proof->proof_id ? 'ID '.$proof->proof_id : null,
                $proof->proof_expiredate ? 'expires '.$this->fmtDate($proof->proof_expiredate) : null,
            ]));
            $this->add($proof->updated_at, 'details', 'Proof of ID updated', $detail, $this->staff($proof->updated_by));
        }
        $fee = DB::table('applicant_fee_eligibilities')->leftJoin('fee_eligibilities', 'fee_eligibilities.id', '=', 'applicant_fee_eligibilities.fee_eligibility_id')
            ->where('applicant_fee_eligibilities.applicant_id', $id)->whereNull('applicant_fee_eligibilities.deleted_at')
            ->orderByDesc('applicant_fee_eligibilities.id')->first(['applicant_fee_eligibilities.*', 'fee_eligibilities.name as fee_name']);
        if ($fee && !empty($fee->updated_by) && $fee->updated_at) {
            $this->add($fee->updated_at, 'details', 'Fee eligibility updated', (string) $fee->fee_name, $this->staff($fee->updated_by));
        }

        if ($after) {
            foreach (DB::table('applicant_qualifications')->where('applicant_id', $id)->where('created_at', '>', $after)->get() as $q) {
                $this->add($q->created_at, 'details', 'Qualification added', trim($q->highest_academic.($q->awarding_body ? ' · '.$q->awarding_body : '')), $this->staff($q->created_by));
            }
            foreach (DB::table('applicant_employments')->where('applicant_id', $id)->where('created_at', '>', $after)->get() as $m) {
                $this->add($m->created_at, 'details', 'Employment added', trim($m->company_name.($m->position ? ' · '.$m->position : '')), $this->staff($m->created_by));
            }
        }
        foreach (DB::table('applicant_qualifications')->where('applicant_id', $id)->whereNotNull('deleted_at')->get() as $q) {
            $this->add($q->deleted_at, 'details', 'Qualification removed', (string) $q->highest_academic, $this->staff($q->updated_by));
        }
        foreach (DB::table('applicant_employments')->where('applicant_id', $id)->whereNotNull('deleted_at')->get() as $m) {
            $this->add($m->deleted_at, 'details', 'Employment removed', (string) $m->company_name, $this->staff($m->updated_by));
        }

        foreach (DB::table('applicant_temporary_emails')->where('applicant_id', $id)->get() as $t) {
            $this->add($t->created_at, 'details', 'Login email change requested', $t->email.' · confirmation link sent', $this->staff($t->created_by));
        }
    }

    private function taskEvents(): void
    {
        $id = $this->a->id;
        $tasks = DB::table('applicant_tasks')->leftJoin('task_lists', 'task_lists.id', '=', 'applicant_tasks.task_list_id')
            ->where('applicant_tasks.applicant_id', $id)
            ->get(['applicant_tasks.*', 'task_lists.name as task_name']);
        if ($tasks->isEmpty() && !DB::table('task_assignment_runs')->where('applicant_id', $id)->exists()) {
            return;
        }
        $names = $tasks->pluck('task_name', 'id')->map(fn ($n) => $n ?: 'Task')->all();
        $outcomes = DB::table('task_statuses')->pluck('name', 'id')->map(fn ($n) => trim(preg_replace('/\(.*\)$/', '', (string) $n)))->all();

        // Task Automation: one event per live run, listing what it created.
        $fromRuns = [];
        $triggers = ['application_submitted' => 'on submission', 'student_created' => 'when the student was created', 'student_status_changed' => 'on a student status change'];
        foreach (DB::table('task_assignment_runs')->where('applicant_id', $id)->whereNull('student_id')->where('mode', 'live')->orderBy('id')->get() as $run) {
            $ids = array_map('intval', json_decode((string) $run->created_task_ids, true) ?: []);
            foreach ($ids as $t) {
                $fromRuns[$t] = true;
            }
            $n = count($ids);
            $when = $triggers[$run->trigger] ?? '';
            $this->add($run->created_at, 'system', $n ? $n.' task'.($n === 1 ? '' : 's').' generated' : 'Task rules checked',
                $n ? trim('Assigned by Task Automation rules '.$when) : 'No rule matched, so no tasks were created', $this->staff($run->created_by), [
                    'items' => $n > 1 ? array_map(fn ($t) => ['time' => null, 'title' => $names[$t] ?? 'Task #'.$t, 'detail' => ''], $ids) : [],
                    'detailOverride' => $n === 1 ? ($names[$ids[0]] ?? '') : null,
                ]);
        }

        // Tasks added outside the rules: generated by the system or added by staff from the board.
        $others = $tasks->reject(fn ($t) => isset($fromRuns[(int) $t->id]));
        foreach ($others->groupBy(fn ($t) => substr((string) $t->created_at, 0, 16).'|'.(int) $t->created_by) as $group) {
            $first = $group->first();
            $who = $this->staff($first->created_by);
            $n = $group->count();
            $list = $group->map(fn ($t) => ['time' => null, 'title' => $names[$t->id], 'detail' => ''])->values()->all();
            if ($who === 'system') {
                $this->add($first->created_at, 'system', $n.' task'.($n === 1 ? '' : 's').' generated', $n === 1 ? $names[$first->id] : 'Created automatically from the process checklist', $who, ['items' => $n > 1 ? $list : []]);
            } else {
                $this->add($first->created_at, 'task', $n === 1 ? $names[$first->id] : $n.' tasks added', $n === 1 ? 'Added to the process board' : 'Added to the process board', $who, ['items' => $n > 1 ? $list : []]);
            }
        }

        $logs = DB::table('applicant_task_logs')->whereIn('applicant_tasks_id', $tasks->pluck('id'))->orderBy('id')->get();
        foreach ($logs as $l) {
            $task = $names[$l->applicant_tasks_id] ?? 'Task';
            $prev = (string) $l->prev_field_value;
            $cur = (string) $l->current_field_value;
            switch ($l->actions) {
                case 'Document':      // the upload itself is a document event
                case 'Assigned':      // covered by the Task Automation run
                case 'E-signature request sent': // the e-signature audit records the request
                    continue 2;
                case 'Status Changed':
                    $detail = $cur === 'Completed' ? 'Marked complete' : ($prev === 'Completed' ? 'Reopened · '.$prev.' → '.$cur : $prev.' → '.$cur);
                    if ($prev === '' ) $detail = 'Set to '.$cur;
                    break;
                case 'Task Status':
                    $new = $outcomes[(int) $cur] ?? $cur;
                    $detail = $prev !== '' ? 'Outcome: '.($outcomes[(int) $prev] ?? $prev).' → '.$new : 'Outcome: '.$new;
                    break;
                case 'Delete':
                    $detail = 'Archived';
                    break;
                case 'Restore':
                    $detail = 'Restored to the board';
                    break;
                case 'Note':
                    $detail = 'Note: '.Str::limit(trim(strip_tags($cur)), 140);
                    break;
                default:
                    $detail = trim($l->actions.($cur !== '' ? ': '.($prev !== '' ? $prev.' → ' : '').$cur : ''));
            }
            $this->add($l->created_at, 'task', $task, $detail, $this->staff($l->created_by), ['merge' => 'task']);
        }
    }

    private function interviewEvents(): void
    {
        $id = $this->a->id;
        $rows = DB::table('applicant_interviews')->leftJoin('applicant_tasks', 'applicant_tasks.id', '=', 'applicant_interviews.applicant_task_id')
            ->leftJoin('task_lists', 'task_lists.id', '=', 'applicant_tasks.task_list_id')
            ->where('applicant_interviews.applicant_id', $id)->orderBy('applicant_interviews.id')
            ->get(['applicant_interviews.*', 'task_lists.name as task_name']);
        foreach ($rows as $r) {
            $result = trim((string) $r->interview_result);
            $hasTime = !empty($r->start_time);
            // Every upload to an interview task leaves a placeholder row (N/A, no time); the upload is already listed.
            if (!$hasTime && ($result === '' || strcasecmp($result, 'N/A') === 0)) {
                continue;
            }
            if ($r->user_id) $this->staffIds[(int) $r->user_id] = true;
            $detail = implode(' · ', array_filter([
                $r->interview_date ? $this->fmtDate($r->interview_date) : null,
                $hasTime ? $this->timeRange($r->start_time, $r->end_time) : null,
                ($result !== '' && strcasecmp($result, 'N/A') !== 0) ? 'Result: '.$result : null,
                $r->interview_status ?: null,
            ]));
            $this->add($r->updated_at ?: $r->created_at, 'task', 'Interview'.($r->task_name ? ' · '.$r->task_name : ''), $detail, $this->staff($r->user_id ?: $r->created_by), [
                'withUser' => $r->user_id && (int) $r->user_id !== (int) $r->created_by ? (int) $r->user_id : null,
            ]);
        }

        foreach (DB::table('applicant_view_unlocks')->where('applicant_id', $id)->orderBy('id')->get() as $u) {
            if ($u->user_id) $this->staffIds[(int) $u->user_id] = true;
            $this->add($u->created_at, 'task', 'Interview profile unlocked', 'One-hour access to the applicant profile for the interview', $this->staff($u->created_by), [
                'merge' => 'unlock',
                'withUser' => $u->user_id && (int) $u->user_id !== (int) $u->created_by ? (int) $u->user_id : null,
            ]);
        }
    }

    private function noteEvents(): void
    {
        $id = $this->a->id;
        $rows = DB::table('applicant_notes')->leftJoin('applicant_documents', 'applicant_documents.id', '=', 'applicant_notes.applicant_document_id')
            ->where('applicant_notes.applicant_id', $id)->orderBy('applicant_notes.id')
            ->get(['applicant_notes.*', 'applicant_documents.display_file_name as file_name', 'applicant_documents.current_file_name as file_current']);
        foreach ($rows as $n) {
            $text = Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', (string) $n->note)), ENT_QUOTES | ENT_HTML5))), 220);
            $this->add($n->created_at, 'note', 'Note added', $text, $this->staff($n->created_by), [
                'file' => $n->file_name ?: $n->file_current,
            ]);
            if ($n->deleted_at) {
                $this->add($n->deleted_at, 'note', 'Note deleted', Str::limit($text, 120), $this->staff($n->updated_by));
            }
        }
    }

    private function communicationEvents(): void
    {
        $id = $this->a->id;

        $attach = DB::table('applicant_emails_attachments')->join('applicant_emails', 'applicant_emails.id', '=', 'applicant_emails_attachments.applicant_email_id')
            ->where('applicant_emails.applicant_id', $id)->whereNull('applicant_emails_attachments.deleted_at')
            ->select('applicant_email_id', DB::raw('count(*) as c'))->groupBy('applicant_email_id')->pluck('c', 'applicant_email_id')->all();
        foreach (DB::table('applicant_emails')->where('applicant_id', $id)->orderBy('id')->get() as $m) {
            $n = (int) ($attach[$m->id] ?? 0);
            $this->add($m->created_at, 'comms', 'Email sent', trim(($m->subject ?: 'No subject').($n ? ' · '.$n.' attachment'.($n === 1 ? '' : 's') : '')), $this->staff($m->created_by));
            if ($m->deleted_at) {
                $this->add($m->deleted_at, 'comms', 'Email deleted', (string) $m->subject, $this->staff($m->updated_by));
            }
        }

        foreach (DB::table('applicant_sms')->where('applicant_id', $id)->orderBy('id')->get() as $s) {
            $this->add($s->created_at, 'comms', 'SMS sent', $s->subject ?: Str::limit(trim(strip_tags((string) $s->sms)), 140), $this->staff($s->created_by));
            if ($s->deleted_at) {
                $this->add($s->deleted_at, 'comms', 'SMS deleted', (string) ($s->subject ?: Str::limit((string) $s->sms, 80)), $this->staff($s->updated_by));
            }
        }

        $letters = DB::table('applicant_letters')->leftJoin('letter_sets', 'letter_sets.id', '=', 'applicant_letters.letter_set_id')
            ->leftJoin('signatories', 'signatories.id', '=', 'applicant_letters.signatory_id')
            ->where('applicant_letters.applicant_id', $id)->orderBy('applicant_letters.id')
            ->get(['applicant_letters.*', 'letter_sets.letter_title', 'signatories.signatory_name']);
        foreach ($letters as $l) {
            $detail = implode(' · ', array_filter([
                $l->letter_title ?: 'Letter',
                $l->issued_date ? 'issued '.$this->fmtDate($l->issued_date) : null,
                $l->signatory_name ? 'signed by '.$l->signatory_name : null,
                $l->comon_smtp_id ? 'emailed to the applicant' : null,
            ]));
            $this->add($l->created_at, 'comms', 'Letter issued', $detail, $this->staff($l->issued_by ?: $l->created_by));
            if ($l->deleted_at) {
                $this->add($l->deleted_at, 'comms', 'Letter deleted', (string) $l->letter_title, $this->staff($l->updated_by));
            }
        }

        foreach (DB::table('admission_notifications')->where('applicant_id', $id)->orderBy('id')->get() as $n) {
            $failed = $n->status !== 'sent';
            $detail = trim(($n->subject ?: Str::headline((string) $n->type)).($n->recipients ? ' · to '.$n->recipients : ''));
            if ($failed) {
                $detail .= ' · not sent'.($n->error ? ': '.Str::limit((string) $n->error, 120) : '');
            }
            $this->add($n->sent_at ?: $n->created_at, 'comms', 'Admissions team notified', $detail, $this->staff($n->user_id ?: self::SYSTEM_USER), ['tone' => $failed ? 'bad' : null]);
        }
    }

    private function documentEvents(): void
    {
        $id = $this->a->id;
        $taskOf = DB::table('applicant_task_documents')->join('applicant_tasks', 'applicant_tasks.id', '=', 'applicant_task_documents.applicant_task_id')
            ->leftJoin('task_lists', 'task_lists.id', '=', 'applicant_tasks.task_list_id')
            ->where('applicant_tasks.applicant_id', $id)->pluck('task_lists.name', 'applicant_task_documents.applicant_document_id')->all();
        $types = DB::table('document_settings')->pluck('name', 'id')->all();

        foreach (DB::table('applicant_documents')->where('applicant_id', $id)->orderBy('id')->get() as $d) {
            if (isset($this->shownDocIds[(int) $d->id])) {
                continue;
            }
            $name = $d->display_file_name ?: $d->current_file_name ?: 'File';
            $task = $taskOf[$d->id] ?? null;
            $type = $d->document_setting_id ? ($types[$d->document_setting_id] ?? null) : null;
            $label = $task ?: ($type ?: 'Uploaded files');
            $hard = (int) $d->hard_copy_check === 1 ? ' · hard copy seen' : '';
            $title = $task ? 'File added to '.$task : ($type ? $type.' uploaded' : 'File uploaded');
            $this->add($d->created_at, 'doc', $title, $name.$hard, $this->staff($d->created_by), [
                'merge' => 'add',
                'item'  => ['title' => $label, 'detail' => $name.$hard],
                'onTask' => (bool) $task,
            ]);
            if ($d->deleted_at) {
                $this->add($d->deleted_at, 'doc', 'File deleted', $name, $this->staff($d->updated_by), [
                    'merge' => 'del',
                    'item'  => ['title' => $label, 'detail' => $name],
                ]);
            }
        }
    }

    private function esignEvents(): void
    {
        $id = $this->a->id;
        $rows = DB::table('applicant_e_signature_events')->where('applicant_id', $id)->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return;
        }
        $titles = [
            'sign_request_created' => 'Sign request created', 'email_sent' => 'Email sent', 'sms_sent' => 'SMS sent', 'email_read' => 'Email read',
            'viewed' => 'Viewed', 'consented_to_esign' => 'Consented to e-sign', 'location_verified' => 'Location verified', 'finalized' => 'Finalised',
        ];
        $applicantEmail = Str::lower((string) optional($this->a->users)->email);
        $emails = $rows->map(fn ($r) => Str::lower((string) (preg_match('/^\s*([^\s\']+@[^\s\']+)/', (string) $r->event_description, $m) ? $m[1] : '')))->filter()->unique()->all();
        $staffByEmail = $emails ? DB::table('users')->whereIn(DB::raw('LOWER(email)'), $emails)->pluck('id', DB::raw('LOWER(email)'))->all() : [];

        foreach ($rows as $r) {
            $desc = (string) $r->event_description;
            $email = preg_match('/^\s*([^\s\']+@[^\s\']+)/', $desc, $m) ? Str::lower($m[1]) : '';
            if (in_array($r->event_type, ['email_sent', 'sms_sent', 'sign_request_created'], true)) {
                $who = 'system';
            } elseif ($r->user_type === 'applicant' || ($email !== '' && $email === $applicantEmail)) {
                $who = 'applicant';
            } elseif ($email !== '' && isset($staffByEmail[$email])) {
                $who = $this->staff($staffByEmail[$email]);
            } else {
                $who = 'system';
            }
            $device = trim(implode(' · ', array_filter([$r->browser !== 'System' ? $r->browser : null, $r->os !== 'System' ? $r->os : null])));
            $detail = trim($desc.($device !== '' && in_array($r->event_type, ['viewed', 'consented_to_esign', 'location_verified', 'finalized'], true) ? ' · '.$device : ''));
            $this->add($r->created_at, 'esign', $titles[$r->event_type] ?? Str::headline((string) $r->event_type), $detail, $who, [
                'merge' => 'esign:'.$r->event_type,
                'item'  => ['title' => $titles[$r->event_type] ?? Str::headline((string) $r->event_type), 'detail' => $device ?: $desc],
            ]);
        }
    }

    /** The DocuSeal offer acceptance form sent at Offer Placed, and what came of each one. */
    private function docusealEvents(): void
    {
        $id = $this->a->id;
        $rows = DB::table('applicant_docuseal_submissions')->where('applicant_id', $id)->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return;
        }
        $docIds = $rows->pluck('offer_document_id')->merge($rows->pluck('audit_document_id'))->filter()->all();
        $docs = $docIds ? DB::table('applicant_documents')->whereIn('id', $docIds)->pluck('display_file_name', 'id')->all() : [];
        foreach ($rows as $s) {
            $form = 'Offer acceptance form';
            if ($s->sent_at) {
                $this->add($s->sent_at, 'esign', $form.' sent', 'DocuSeal'.($s->applicant_email ? ' · to '.$s->applicant_email : ''), $this->staff($s->sent_by ?: self::SYSTEM_USER));
            }
            if ($s->opened_at) {
                $this->add($s->opened_at, 'esign', $form.' opened', 'The applicant opened the DocuSeal link', 'applicant');
            }
            if ($s->completed_at) {
                $this->add($s->completed_at, 'esign', $form.' signed', 'Signed on DocuSeal', 'applicant', ['milestone' => true]);
            }
            if ($s->state === 'declined' || $s->docuseal_status === 'declined') {
                $this->add($s->outcome_at ?: $s->updated_at, 'esign', 'Offer declined', $s->decline_reason ? '“'.Str::limit((string) $s->decline_reason, 200).'”' : 'Declined on DocuSeal', 'applicant', ['milestone' => true, 'tone' => 'bad']);
            }
            if ($s->stored_at) {
                $files = array_values(array_filter([$docs[$s->offer_document_id] ?? null, $docs[$s->audit_document_id] ?? null]));
                $this->add($s->stored_at, 'system', 'Signed documents stored', $files ? implode(' · ', $files) : 'Added to Uploaded files', 'system');
            }
            $outcomes = [
                'held' => ['Held for proof of ID', 'The form was signed but the proof of ID is incomplete', 'warn'],
                'conversion_failed' => ['Move to Students stopped', null, 'bad'],
            ];
            if ($s->outcome && isset($outcomes[$s->outcome])) {
                [$t, $d, $tone] = $outcomes[$s->outcome];
                $note = $d ?? Str::limit(strtok((string) $s->outcome_note, "\n") ?: 'See the conversion log', 180);
                $this->add($s->outcome_at ?: $s->updated_at, 'system', $t, $note, 'system', ['tone' => $tone]);
            }
        }
    }

    /** One event per student conversion batch: completed, stopped or still running. */
    private function conversionEvents(): void
    {
        $id = $this->a->id;
        $rows = DB::table('student_conversion_logs')->where('applicant_id', $id)->whereNull('deleted_at')->orderBy('id')->get();
        foreach ($rows->groupBy('batch_id') as $batch) {
            $total = $batch->count();
            $done = $batch->where('status', 'completed')->count();
            $failed = $batch->firstWhere('status', 'failed');
            $pending = $batch->whereIn('status', ['queued', 'processing'])->count();
            $started = $batch->pluck('started_at')->filter()->min() ?: $batch->min('created_at');
            $finished = $batch->pluck('finished_at')->filter()->max() ?: $batch->max('updated_at');
            $studentId = $batch->pluck('student_id')->filter()->first();
            $reg = $studentId ? DB::table('students')->where('id', $studentId)->value('registration_no') : null;
            $who = $this->staff($batch->first()->created_by);
            $logUrl = route('admission.conversion.log', $id);

            if ($failed) {
                $this->add($finished, 'system', 'Move to Students stopped', 'Failed at '.($failed->job_name ?: class_basename((string) $failed->job_class)).($failed->message ? ': '.Str::limit(strtok((string) $failed->message, "\n"), 140) : '').' · '.$done.' of '.$total.' jobs done', $who, [
                    'tone' => 'bad', 'href' => $logUrl, 'hrefLabel' => 'Conversion log',
                ]);
            } elseif ($pending) {
                $this->add($started, 'system', 'Moving to Students', $done.' of '.$total.' jobs done so far', $who, ['href' => $logUrl, 'hrefLabel' => 'Conversion log']);
            } else {
                $this->add($finished, 'system', 'Converted to student', implode(' · ', array_filter([
                    $reg ? 'Student '.$reg : ($studentId ? 'Student #'.$studentId : null),
                    $done.' jobs completed',
                ])), $who, [
                    'milestone' => true,
                    'href' => $studentId ? route('student.show', $studentId) : $logUrl,
                    'hrefLabel' => $studentId ? 'View student' : 'Conversion log',
                ]);
            }
        }
    }

    /** Staff time on the record (applicant_view_sessions), one event per visit. */
    private function viewEvents(): void
    {
        $id = $this->a->id;
        $rows = DB::table('applicant_view_sessions')->where('applicant_id', $id)->orderBy('started_at')->get();
        foreach ($rows->groupBy('user_id') as $userId => $sessions) {
            $visit = null;
            $flush = function () use (&$visit, $userId) {
                if (!$visit) return;
                $labels = array_map(fn ($s) => self::SECTIONS[$s] ?? Str::headline($s), array_keys($visit['sections']));
                $detail = (count($labels) <= 3 ? implode(', ', $labels) : count($labels).' sections').' · '.$this->duration($visit['seconds']).' active';
                $items = [];
                foreach ($visit['sections'] as $sec => $agg) {
                    $items[] = ['time' => null, 'title' => self::SECTIONS[$sec] ?? Str::headline($sec), 'detail' => $agg['n'].' page view'.($agg['n'] === 1 ? '' : 's').' · '.$this->duration($agg['s'])];
                }
                $this->add($visit['start'], 'views', 'Viewed the record', $detail, $this->staff($userId), [
                    'items' => count($items) > 1 ? $items : [],
                    'endAt' => $visit['end'],
                ]);
                $visit = null;
            };
            foreach ($sessions as $s) {
                $start = Carbon::parse($s->started_at);
                $end = Carbon::parse($s->last_seen_at ?: $s->started_at);
                if ($visit && $start->diffInMinutes($visit['end'], true) > self::VISIT_GAP_MINUTES) {
                    $flush();
                }
                if (!$visit) {
                    $visit = ['start' => $start, 'end' => $end, 'sections' => [], 'seconds' => 0];
                }
                $visit['end'] = $end->gt($visit['end']) ? $end : $visit['end'];
                $agg = $visit['sections'][$s->section] ?? ['n' => 0, 's' => 0];
                $visit['sections'][$s->section] = ['n' => $agg['n'] + 1, 's' => $agg['s'] + (int) $s->active_seconds];
                $visit['seconds'] += (int) $s->active_seconds;
            }
            $flush();
        }
    }

    /* ------------------------------------------------------------------ assembly */

    private function add($at, string $cat, string $title, ?string $detail, string $who, array $o = []): void
    {
        if (empty($at)) {
            return;
        }
        $at = $at instanceof Carbon ? $at : Carbon::parse($at);
        $untimed = !empty($o['untimed']);
        $this->events[] = [
            'seq'       => ++$this->seq,
            'at'        => $at->format('Y-m-d H:i:s'),
            'date'      => $at->toDateString(),
            'time'      => $untimed ? null : $at->format('H:i'),
            'end'       => isset($o['endAt']) && !$untimed && Carbon::parse($o['endAt'])->format('H:i') !== $at->format('H:i') && Carbon::parse($o['endAt'])->isSameDay($at) ? Carbon::parse($o['endAt'])->format('H:i') : null,
            'cat'       => $cat,
            'title'     => $title,
            'detail'    => (string) ($o['detailOverride'] ?? $detail ?? ''),
            'who'       => $who,
            'with'      => $o['withUser'] ?? null,
            'milestone' => !empty($o['milestone']),
            'tone'      => $o['tone'] ?? null,
            'items'     => $o['items'] ?? [],
            'file'      => $o['file'] ?? null,
            'href'      => $o['href'] ?? null,
            'hrefLabel' => $o['hrefLabel'] ?? null,
            'merge'     => $o['merge'] ?? null,
            'item'      => $o['item'] ?? null,
            'onTask'    => $o['onTask'] ?? null,
        ];
    }

    /**
     * Fold a burst — the same kind of action by the same person within a few
     * minutes, with no status change in between — into its first event.
     */
    private function mergeBursts(array $events): array
    {
        $out = [];
        $open = []; // merge key => index in $out
        foreach ($events as $e) {
            if ($e['milestone']) {
                $open = [];
                $out[] = $e;
                continue;
            }
            if (!$e['merge']) {
                $out[] = $e;
                continue;
            }
            $key = $e['cat'].'|'.$e['merge'].'|'.$e['who'];
            $own = ['time' => $e['time'], 'title' => $e['item']['title'] ?? $e['title'], 'detail' => $e['item']['detail'] ?? $e['detail'], 'onTask' => $e['onTask']];
            if (isset($open[$key])) {
                $g = &$out[$open[$key]];
                $last = Carbon::parse($g['lastAt'] ?? $g['at']);
                if (Carbon::parse($e['at'])->diffInMinutes($last, true) <= self::BURST_MINUTES) {
                    $g['group'][] = $own;
                    $g['lastAt'] = $e['at'];
                    unset($g);
                    continue;
                }
                unset($g);
            }
            $e['group'] = [$own];
            $open[$key] = count($out);
            $out[] = $e;
        }

        foreach ($out as &$e) {
            $group = $e['group'] ?? null;
            $lastAt = $e['lastAt'] ?? null;
            unset($e['group'], $e['item'], $e['onTask'], $e['seq'], $e['lastAt']);
            if (!$group || count($group) < 2) {
                continue;
            }
            $n = count($group);
            $e['items'] = array_map(fn ($i) => ['time' => $i['time'], 'title' => $i['title'], 'detail' => $i['detail']], $group);
            $e['end'] = ($e['time'] && $lastAt && substr($lastAt, 11, 5) !== $e['time']) ? substr($lastAt, 11, 5) : null;
            if ($e['cat'] === 'doc') {
                $deleted = $e['merge'] === 'del';
                $e['title'] = $n.' files '.($deleted ? 'deleted' : 'added');
                $onTasks = count(array_filter($group, fn ($i) => $i['onTask']));
                $e['detail'] = $deleted ? 'Removed from Uploaded files' : ($onTasks === $n ? 'Uploaded against task checklists' : ($onTasks ? 'Uploaded to tasks and the record' : 'Uploaded to the record'));
            } elseif ($e['cat'] === 'task' && $e['merge'] === 'task') {
                $tasks = count(array_unique(array_column($group, 'title')));
                $details = array_unique(array_column($group, 'detail'));
                $e['title'] = $tasks === $n ? $n.' tasks updated' : $n.' updates on '.$tasks.' task'.($tasks === 1 ? '' : 's');
                $e['detail'] = count($details) === 1 ? $details[0] : 'Several changes';
                if (count($details) === 1) {
                    $e['items'] = array_map(fn ($i) => ['time' => $i['time'], 'title' => $i['title'], 'detail' => ''], $e['items']);
                }
            } elseif ($e['cat'] === 'task' && $e['merge'] === 'unlock') {
                $e['title'] = 'Interview profile unlocked '.$n.' times';
            } elseif ($e['cat'] === 'esign') {
                $e['title'] .= ' · '.$n.' times';
            }
        }
        unset($e);

        foreach ($out as &$e) {
            unset($e['merge']);
        }
        unset($e);

        return $out;
    }

    /** key => [kind, name, ini, role] for every actor the events mention. */
    private function actors(array $events): array
    {
        $a = $this->a;
        $name = trim($a->first_name.' '.$a->last_name);
        $actors = [
            'system'    => ['kind' => 'system', 'name' => 'System', 'ini' => 'SY', 'role' => 'Automated'],
            'applicant' => ['kind' => 'applicant', 'name' => $name ?: 'Applicant', 'ini' => Avatar::initialsOnly($name ?: 'Applicant'), 'role' => 'Applicant'],
            'unknown'   => ['kind' => 'system', 'name' => 'Not recorded', 'ini' => '—', 'role' => 'Who did this was not saved'],
        ];
        if (!empty($a->agent_user_id)) {
            $agent = DB::table('agents')->where('agent_user_id', $a->agent_user_id)->first();
            $agentName = $agent ? ($agent->organization ?: trim($agent->first_name.' '.$agent->last_name)) : 'Agent';
            $actors['agent'] = ['kind' => 'agent', 'name' => $agentName, 'ini' => Avatar::initialsOnly($agentName), 'role' => 'Agent'];
        }

        foreach ($events as $e) {
            if (str_starts_with($e['who'], 'u:')) $this->staffIds[(int) substr($e['who'], 2)] = true;
        }
        $ids = array_keys($this->staffIds);
        if ($ids) {
            $rows = DB::table('users')->leftJoin('employees', 'employees.user_id', '=', 'users.id')
                ->leftJoin('employments', 'employments.employee_id', '=', 'employees.id')
                ->leftJoin('employee_job_titles', 'employee_job_titles.id', '=', 'employments.employee_job_title_id')
                ->whereIn('users.id', $ids)
                ->get(['users.id', 'users.name', 'employees.first_name', 'employees.last_name', 'employee_job_titles.name as job']);
            foreach ($rows->groupBy('id') as $uid => $r) {
                $first = $r->first();
                $full = trim(($first->first_name ?? '').' '.($first->last_name ?? ''));
                $full = $this->nameCase($full !== '' ? $full : (string) $first->name);
                $job = $r->pluck('job')->filter()->first();
                $actors['u:'.$uid] = ['kind' => 'staff', 'name' => $full, 'ini' => Avatar::initialsOnly($full), 'role' => $job ?: 'Staff'];
            }
        }
        foreach ($events as $e) {
            if (!isset($actors[$e['who']])) {
                $actors[$e['who']] = ['kind' => 'staff', 'name' => 'User #'.substr($e['who'], 2), 'ini' => '#', 'role' => 'Staff'];
            }
        }

        return $actors;
    }

    private function people(array $events, array $actors): array
    {
        $count = [];
        foreach ($events as $e) {
            $count[$e['who']] = ($count[$e['who']] ?? 0) + 1;
        }
        arsort($count);
        $out = [];
        foreach ($count as $key => $n) {
            $out[] = ['key' => $key, 'n' => $n] + $actors[$key];
        }
        return $out;
    }

    private function counts(array $events): array
    {
        $c = array_fill_keys(array_keys(self::CATEGORIES), 0);
        foreach ($events as $e) {
            $c[$e['cat']]++;
        }
        return $c;
    }

    private function days(array $events): array
    {
        $days = [];
        $first = $events ? Carbon::parse($events[0]['date']) : null;
        foreach ($events as $i => $e) {
            if (!isset($days[$e['date']])) {
                $d = Carbon::parse($e['date']);
                $days[$e['date']] = ['date' => $e['date'], 'label' => $d->format('D jS M, Y'), 'n' => $first->diffInDays($d) + 1, 'events' => []];
            }
            $days[$e['date']]['events'][] = $i;
        }
        return array_values($days);
    }

    private function summary(array $events): array
    {
        if (!$events) {
            return ['started' => null, 'latest' => null, 'elapsed' => 0, 'events' => 0];
        }
        $first = Carbon::parse($events[0]['at']);
        $last = Carbon::parse(end($events)['at']);
        return [
            'started' => $first->format('D jS M, Y').($events[0]['time'] ? ' · '.$events[0]['time'] : ''),
            'latest'  => $last->format('D jS M, Y · H:i'),
            'elapsed' => (int) Carbon::parse($first->toDateString())->diffInDays(Carbon::parse($last->toDateString())),
            'events'  => count($events),
        ];
    }

    /** The closing card: where the application is now. */
    private function outcome(): array
    {
        $s = (int) $this->a->status_id;
        $text = [
            1 => 'Draft — the applicant has not submitted it yet.',
            2 => 'Waiting for a staff member to start the tasks.',
            3 => 'Tasks in progress.',
            4 => 'All tasks complete — ready for a decision.',
            5 => 'Accepted — the offer is to be placed.',
            6 => 'Waiting for the applicant to sign the offer acceptance form.',
            7 => 'Offer accepted — moved to Students.',
            8 => 'Application rejected.',
            9 => 'The applicant declined the offer.',
            46 => 'Application cancelled.',
        ][$s] ?? '';
        $tone = [1 => 'muted', 2 => 'muted', 3 => 'amber', 4 => 'blue', 5 => 'green', 6 => 'blue', 7 => 'green', 8 => 'red', 9 => 'red', 46 => 'red'][$s] ?? 'muted';
        return [
            'final'  => in_array($s, [7, 8, 9, 46], true),
            'status' => $this->statusName($s),
            'tone'   => $tone,
            'text'   => $text,
        ];
    }

    /* ------------------------------------------------------------------ helpers */

    /** users.id → actor key; user 1 (and a missing id) is the System account. */
    private function staff($userId): string
    {
        $id = (int) $userId;
        if ($id <= 0) {
            return 'unknown';
        }
        if ($id === self::SYSTEM_USER) {
            return 'system';
        }
        $this->staffIds[$id] = true;
        return 'u:'.$id;
    }

    private function statusName(int $id): string
    {
        return $this->statusNames[$id] ?? ($id ? 'Status #'.$id : 'Draft');
    }

    private function courseLine(): string
    {
        $c = $this->a->course;
        return trim(implode(' · ', array_filter([
            optional(optional(optional($c)->creation)->course)->name,
            optional(optional($c)->semester)->name,
        ])));
    }

    private function fmtDate($value): string
    {
        foreach (['Y-m-d', 'd-m-Y', 'Y-m-d H:i:s'] as $f) {
            try {
                return Carbon::createFromFormat($f, (string) $value)->format('d M Y');
            } catch (\Throwable $e) {
            }
        }
        return (string) $value;
    }

    /** "HUSNA SAHAK" → "Husna Sahak"; mixed-case names are left as typed. */
    private function nameCase(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));
        return $name !== '' && $name === Str::upper($name) ? Str::title(Str::lower($name)) : $name;
    }

    private function timeRange($start, $end): string
    {
        $f = fn ($t) => $t ? substr((string) $t, 0, 5) : '';
        return $f($end) !== '' && $f($end) !== $f($start) ? $f($start).'–'.$f($end) : $f($start);
    }

    private function duration(int $seconds): string
    {
        if ($seconds < 60) {
            return max($seconds, 0).' s';
        }
        $m = intdiv($seconds, 60);
        return $m < 60 ? $m.' min' : intdiv($m, 60).' h '.($m % 60).' min';
    }
}
