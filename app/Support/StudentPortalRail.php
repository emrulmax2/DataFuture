<?php

namespace App\Support;

use App\Models\NewsAndEvent;
use App\Models\StudentSms;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Data for the student portal right rail (news & updates, today's / upcoming
 * classes).
 *
 * The rail is part of the portal *shell*, so every screen that extends
 * `layout/student-portal` renders it — not just the two controllers that used
 * to hand this data to the old sidebar partial. Gathering it here (and behind
 * a per-request memo) keeps the controllers untouched and stops the same
 * queries running twice on a single page.
 */
class StudentPortalRail
{
    /** @var array<int, array> */
    protected static $memo = [];

    /**
     * The student whose record the portal is currently showing — the one the
     * session has selected, otherwise the signed-in user's latest enrolment.
     *
     * The shell resolves this itself so screens that never needed a `$student`
     * (module detail, orders, IT reports) still get a complete sidebar and rail.
     *
     * @return \App\Models\Student|null
     */
    public static function current()
    {
        if (!auth('student')->check()) {
            return null;
        }

        $selectedId = session('selected_student_id');

        if ($selectedId) {
            return \App\Models\Student::find($selectedId);
        }

        return \App\Models\Student::where('student_user_id', auth('student')->user()->id)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * @param  \App\Models\Student|null  $student
     * @return array{news: \Illuminate\Support\Collection, today: \Illuminate\Support\Collection, upcoming: \Illuminate\Support\Collection}
     */
    public static function for($student)
    {
        if (!isset($student->id)) {
            return [
                'news' => collect(),
                'today' => collect(),
                'upcoming' => collect(),
            ];
        }

        if (isset(static::$memo[$student->id])) {
            return static::$memo[$student->id];
        }

        $classes = static::classes($student->id);

        return static::$memo[$student->id] = [
            'news' => static::news($student->id),
            'today' => $classes['today'],
            'upcoming' => $classes['upcoming'],
        ];
    }

    /**
     * News & events targeted at everyone or at this student, plus any SMS the
     * college flagged to surface as news — normalised into one shape.
     */
    protected static function news($studentId)
    {
        /* An item must be active to reach anyone. Beyond that there are two
           ways a student sees one: it is a college-wide broadcast (fol_all),
           or it was addressed to them individually. */
        $events = NewsAndEvent::with('documents')
            ->where('active', 1)
            ->where(function ($query) use ($studentId) {
                $query->where('fol_all', 1)
                    ->orWhereHas('students', function ($targeted) use ($studentId) {
                        $targeted->where('student_id', $studentId);
                    });
            })
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function ($event) {
                return (object) [
                    'title' => $event->title,
                    'age' => $event->created_at_human_time,
                    'body' => $event->content,
                    'documents' => $event->documents,
                ];
            });

        $sms = StudentSms::with('sms')
            ->where('student_id', $studentId)
            ->where('show_as_news', 1)
            ->orderBy('id', 'DESC')
            ->get()
            ->filter(function ($row) {
                return !empty($row->sms->sms);
            })
            ->map(function ($row) {
                return (object) [
                    'title' => !empty($row->sms->subject) ? $row->sms->subject : 'Message from the college',
                    'age' => $row->created_at_human_time,
                    'body' => $row->sms->sms,
                    'documents' => collect(),
                ];
            });

        return $events->concat($sms)->values();
    }

    /**
     * Scheduled sessions the student is assigned to, split into today's and
     * the next few upcoming ones.
     */
    protected static function classes($studentId)
    {
        $today = Carbon::now()->startOfDay();

        $rows = DB::table('plans_date_lists as pdl')
            ->join('plans as plan', 'pdl.plan_id', '=', 'plan.id')
            ->join('assigns as assign', 'assign.plan_id', '=', 'plan.id')
            ->leftJoin('module_creations as module', 'plan.module_creation_id', '=', 'module.id')
            ->leftJoin('venues as venue', 'plan.venue_id', '=', 'venue.id')
            ->leftJoin('rooms as room', 'plan.rooms_id', '=', 'room.id')
            ->where('assign.student_id', $studentId)
            ->whereNull('pdl.deleted_at')
            ->whereNull('plan.deleted_at')
            ->whereNull('assign.deleted_at')
            ->whereDate('pdl.date', '>=', $today->toDateString())
            ->orderBy('pdl.date', 'ASC')
            ->orderBy('plan.start_time', 'ASC')
            ->limit(40)
            ->select(
                'pdl.date as date',
                'plan.start_time as start_time',
                'plan.end_time as end_time',
                'plan.class_type as class_type',
                'plan.virtual_room as virtual_room',
                'module.module_name as module_name',
                'module.class_type as module_class_type',
                'venue.name as venue_name',
                'room.name as room_name'
            )
            ->get()
            ->map(function ($row) {
                $where = array_filter([$row->venue_name, $row->room_name]);

                return (object) [
                    'date' => $row->date,
                    'module' => $row->module_name,
                    'class_type' => $row->class_type ?: $row->module_class_type,
                    'day' => date('D j M', strtotime($row->date)),
                    'time' => date('h:i A', strtotime('2000-01-01 ' . $row->start_time))
                        . ' – ' . date('h:i A', strtotime('2000-01-01 ' . $row->end_time)),
                    'where' => implode(', ', $where),
                    'virtual_room' => $row->virtual_room,
                ];
            });

        $todayKey = $today->toDateString();

        return [
            'today' => $rows->filter(function ($row) use ($todayKey) {
                return date('Y-m-d', strtotime($row->date)) === $todayKey;
            })->take(6)->values(),
            'upcoming' => $rows->filter(function ($row) use ($todayKey) {
                return date('Y-m-d', strtotime($row->date)) > $todayKey;
            })->take(6)->values(),
        ];
    }
}
