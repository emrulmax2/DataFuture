<?php

namespace App\Services\ClassRoutine;

use App\Models\StudentContact;
use Carbon\Carbon;

/**
 * Puts a student's class dates in their @lcc.ac.uk Google Calendar (user, 28 Oct 2026).
 *
 * One event per class date of their current-term plans (ClassRoutineBuilder::classDates), from
 * today on; past classes are left as they are. Each event's id comes from the class date and the
 * student, so running it again updates rather than duplicates. A cancelled or deleted class date
 * removes its event, and so does a class the student is no longer assigned to.
 *
 * Off production the events go to config('class_routine.calendar.redirect_to') (default
 * MAIL_REDIRECT_TO) with the student's registration number in the title, never to the student.
 */
class ClassCalendarSync
{
    public function __construct(private ClassRoutineBuilder $builder, private ClassCalendar $calendar)
    {
    }

    /** Google Calendar event id: base32hex (0-9, a-v), unique per class date and student. */
    public static function eventId(int $plansDateListId, int $studentId): string
    {
        return 'lcc'.$plansDateListId.'s'.$studentId;
    }

    /**
     * @return array{status:string, message:string, created:int, updated:int, removed:int, calendar:?string}
     * @throws \RuntimeException when Google refuses (the job retries)
     */
    public function sync(int $studentId): array
    {
        $result = fn (string $status, string $message, array $counts = [], ?string $calendar = null) => ['status' => $status, 'message' => $message, 'calendar' => $calendar]
            + $counts + ['created' => 0, 'updated' => 0, 'removed' => 0];

        if (!config('class_routine.calendar.enabled')) {
            return $result('off', 'Calendar sync is off (CLASS_CALENDAR_ENABLED).');
        }
        if (!$this->calendar->configured()) {
            return $result('not_configured', 'Google Workspace credentials are not set (GOOGLE_WORKSPACE_CREDENTIALS).');
        }
        $classes = $this->builder->classDates($studentId);
        if (!$classes) {
            return $result('no_classes', 'No class plan is assigned to this student yet.');
        }
        [$owner, $redirected] = $this->owner($studentId);
        if (!$owner) {
            return $result('no_calendar', app()->environment('production')
                ? 'The student has no @'.config('student_email.domain').' address.'
                : 'No calendar to use off production (set CLASS_CALENDAR_REDIRECT_TO or MAIL_REDIRECT_TO).');
        }

        $tz = (string) config('class_routine.calendar.time_zone', 'Europe/London');
        $today = Carbon::now($tz)->startOfDay();
        $counts = ['created' => 0, 'updated' => 0, 'removed' => 0];
        $wanted = [];

        foreach ($classes['dates'] as $class) {
            if ($class['date'] < $today->toDateString()) {
                continue;
            }
            $id = self::eventId($class['plans_date_list_id'], $studentId);
            if ($class['cancelled']) {
                $counts['removed'] += $this->calendar->delete($owner, $id) ? 1 : 0;
                continue;
            }
            $wanted[$id] = true;
            $counts[$this->calendar->upsert($owner, $this->event($id, $class, $classes, $studentId, $tz, $redirected)) === 'created' ? 'created' : 'updated']++;
        }

        // a class the student is no longer assigned to
        foreach ($this->calendar->eventIds($owner, $studentId, $today->toRfc3339String()) as $id) {
            if (!isset($wanted[$id]) && $this->calendar->delete($owner, $id)) {
                $counts['removed']++;
            }
        }

        return $result('done', sprintf('Calendar %s: %d added, %d updated, %d removed.', $owner, $counts['created'], $counts['updated'], $counts['removed']), $counts, $owner);
    }

    /** [calendar owner, redirected?]: the student's @lcc.ac.uk address, or the test calendar off production. */
    private function owner(int $studentId): array
    {
        if (!app()->environment('production')) {
            $to = (string) (config('class_routine.calendar.redirect_to') ?: config('mail.redirect_to'));

            return [$to !== '' ? $to : null, true];
        }
        $email = strtolower(trim((string) optional(StudentContact::where('student_id', $studentId)->orderByDesc('id')->first())->institutional_email));

        return [$email !== '' && str_ends_with($email, '@'.strtolower((string) config('student_email.domain', 'lcc.ac.uk'))) ? $email : null, false];
    }

    private function event(string $id, array $class, array $classes, int $studentId, string $tz, bool $redirected): array
    {
        $online = $class['mode'] === 'online';
        $where = $online ? ($class['link'] ?: 'Online') : implode(', ', array_filter([$class['room'], $class['venue']]));
        $details = array_filter([
            $class['kind'].' · '.$class['title'],
            $online ? ($class['link'] ? 'Online class link: '.$class['link'] : 'Online class') : ($class['room'] ? 'Room: '.$class['room'] : null),
            $class['person'] ? $class['person_label'].': '.$class['person'] : null,
            'Group: '.$classes['group'].' · '.$classes['term'],
            'London Churchill College',
        ]);

        return [
            'id' => $id,
            'status' => 'confirmed',
            'summary' => ($redirected ? '['.$classes['student']['registration_no'].'] ' : '').$class['kind'].' · '.$class['title'],
            'location' => $where,
            'description' => implode("\n", $details),
            'start' => ['dateTime' => $class['date'].'T'.$class['start'].':00', 'timeZone' => $tz],
            'end' => ['dateTime' => $class['date'].'T'.$class['end'].':00', 'timeZone' => $tz],
            'colorId' => $online ? '5' : '10', // banana for online, basil for campus, as on the routine
            'reminders' => ['useDefault' => true],
            'extendedProperties' => ['private' => [
                'lcc_student' => (string) $studentId,
                'lcc_plan' => (string) $class['plan_id'],
                'lcc_plan_date' => (string) $class['plans_date_list_id'],
            ]],
        ];
    }
}
