<?php

namespace App\Services\ClassRoutine;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Everything the class routine (ICP) shows for one student, read from their class plans
 * (user, 28 Oct 2026): the student's current attendance term is the newest term they are
 * assigned to (assigns → plans.term_declaration_id); the routine is every plan they are
 * assigned to in that term.
 *
 *   plans.class_type  Theory → Lecture (campus, plans.tutor_id),
 *                     Tutorial → Tutorial (online when the room is "Online"; link in
 *                     plans.virtual_room; plans.personal_tutor_id),
 *                     Seminar → Workshop (plans.personal_tutor_id)
 *   days / times      plans.mon … plans.sun, start_time / end_time
 *   teaching weeks    the course's instance term (teaching and revision dates), ticked from
 *                     the plans' class dates (plans_date_lists, cancelled ones left out)
 *   deadlines         plans.submission_date of the lectures (else module_creations.submission_date)
 *   level             module_creations.unit_value ("4" → Level 4)
 *   Y1T1              the instance's place in its course creation, and instance_terms.session_term
 */
class ClassRoutineBuilder
{
    public const DAYS = ['mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'];

    /** plans.class_type → what the routine calls it */
    public const KINDS = ['Theory' => 'Lecture', 'Tutorial' => 'Tutorial', 'Seminar' => 'Workshop'];

    private const CANCELLED = ['Canceled', 'Cancelled'];

    /**
     * @param int|null $termDeclarationId this term's plans (the plan tree's Send Class ICP); null: the newest term
     * @return array|null null when the student has no class plan (in that term)
     */
    public function forStudent(int $studentId, ?int $termDeclarationId = null): ?array
    {
        $loaded = $this->assignedPlans($studentId, $termDeclarationId);
        if (!$loaded) {
            return null;
        }
        [$student, $term, $plans] = $loaded;

        $lectures = $plans->where('class_type', 'Theory');
        $main = $lectures->isNotEmpty() ? $lectures : $plans;
        $groupId = (int) $this->mostCommon($main->pluck('group_id'));
        $courseId = (int) $this->mostCommon($main->pluck('course_id'));
        $venueId = (int) $this->mostCommon($main->pluck('venue_id'));
        $instanceTermId = (int) $this->mostCommon($main->pluck('instance_term_id'));

        $people = $this->people($plans->pluck('tutor_id')->merge($plans->pluck('personal_tutor_id')));
        $group = DB::table('groups')->where('id', $groupId)->first();
        $course = DB::table('courses')->where('id', $courseId)->first();
        $awardingBody = $course ? DB::table('awarding_bodies')->where('id', $course->awarding_body_id)->value('name') : null;
        $venue = DB::table('venues')->where('id', $venueId)->first();
        $instanceTerm = DB::table('instance_terms')->where('id', $instanceTermId)->whereNull('deleted_at')->first();

        $venues = DB::table('venues')->whereIn('id', $plans->pluck('venue_id')->filter()->unique())->get(['id', 'name', 'address'])->keyBy('id');
        $sessions = $plans->mapWithKeys(fn ($p) => [(int) $p->id => $this->session($p, $people) + $this->place($p, $venues[$p->venue_id ?? 0] ?? null)]);
        $slots = $sessions->map(fn ($s) => $s['start'].'-'.$s['end'])->unique()->sort()->values()
            ->map(fn ($key) => ['start' => explode('-', $key)[0], 'end' => explode('-', $key)[1]])->all();
        $days = $this->days($sessions, $slots);
        $leaders = DB::table('group_leaders')->where('group_id', $groupId)->where('term_declaration_id', $term->id)->whereNull('deleted_at')->pluck('user_id');

        $level = $this->mostCommon($main->pluck('unit_value')->filter(fn ($v) => is_numeric($v)));
        $teachingStart = $instanceTerm->teaching_start_date ?? $term->teaching_start_date ?? $term->start_date;
        $teachingEnd = $instanceTerm->teaching_end_date ?? $term->teaching_end_date ?? $term->end_date;
        $weeks = $this->weeks($plans, $instanceTerm, $term, $teachingStart);
        // the group's online classes share one Meet room: a class with no link of its own gets the
        // one link the others have (shown as a warning before sending); none or several: as entered
        $links = $sessions->where('mode', 'online')->pluck('link')->filter()->unique()->values();
        $warnings = [];
        if ($links->count() === 1) {
            $missing = $sessions->filter(fn ($s) => $s['mode'] === 'online' && !$s['link']);
            if ($missing->isNotEmpty()) {
                $warnings[] = $missing->count().' online '.($missing->count() === 1 ? 'class has' : 'classes have').' no link of '.($missing->count() === 1 ? 'its' : 'their').' own ('.$missing->pluck('title')->unique()->implode(', ').'); the group\'s link '.$links->first().' is shown for '.($missing->count() === 1 ? 'it' : 'them').'.';
                $sessions = $sessions->map(fn ($s) => $s['mode'] === 'online' && !$s['link'] ? ['link' => $links->first(), 'link_shared' => true] + $s : $s);
            }
        } else {
            foreach ($sessions->filter(fn ($s) => $s['mode'] === 'online' && !$s['link']) as $s) {
                $warnings[] = 'The online class "'.$s['title'].'" has no link; the routine says "link to follow".';
            }
        }
        $online = $sessions->where('mode', 'online');
        $days = $this->days($sessions, $slots); // again, with the shared links in

        return [
            'student' => [
                'id' => (int) $student->id,
                'registration_no' => $student->registration_no,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
            ],
            'term' => [
                'id' => (int) $term->id,
                'name' => $this->termName($term->name),
                'code' => $this->yearTerm($instanceTerm),
                'teaching_start' => $teachingStart,
                'teaching_end' => $teachingEnd,
            ],
            'course' => $this->courseName($course->name ?? ''),
            'group' => [
                'id' => $groupId,
                'name' => $group->name ?? '',
                'size' => $this->groupSize($groupId, (int) $term->id),
                'leaders' => $leaders->map(fn ($id) => $this->people(collect([$id]))[$id] ?? null)->filter()->values()->all(),
            ],
            'level' => $level !== null ? 'Level '.$level : null,
            'awarding_body' => $this->awardingBody($awardingBody),
            'venue' => [
                'name' => $venue->name ?? '',
                'address' => $this->address($venue->address ?? ''),
            ],
            'slots' => $slots,
            'days' => $days,
            'support_days' => array_keys(array_filter($days, fn ($d) => $d['type'] === 'support')),
            'has_campus' => $sessions->contains(fn ($s) => $s['mode'] === 'campus'),
            'has_online' => $sessions->contains(fn ($s) => $s['mode'] === 'online'),
            'online_provider' => $sessions->contains(fn ($s) => str_contains((string) $s['link'], 'meet.google.com')) ? 'Google Meet' : null,
            'weeks' => $weeks,
            // the detailed pages (user, 30 Oct 2026): each week's real class dates, cancelled ones marked
            'calendar' => $this->calendar($plans, $sessions, $weeks),
            // one link for every online class: the overview's banner
            'shared_link' => $online->isNotEmpty() && $links->count() === 1 ? $links->first() : null,
            'online_days' => collect(self::DAYS)->filter(fn ($name, $key) => $online->contains(fn ($s) => in_array($key, $s['days'], true)))->values()->all(),
            'break_minutes' => $this->breakMinutes($slots),
            'campus_rooms' => $sessions->where('mode', 'campus')->pluck('room_label')->filter()->unique()->values()->all(),
            'campus_place' => $this->campusPlace($sessions->where('mode', 'campus')->pluck('building')->filter()->first(), $sessions->where('mode', 'campus')->pluck('town')->filter()->first()),
            'assessments' => $assessments = $this->assessments($lectures, $people),
            'warnings' => array_merge($warnings, collect($assessments)->whereNull('date')->map(fn ($a) => 'No submission date for "'.$a['module'].'"; the routine says "to be confirmed".')->values()->all()),
            'ref' => 'ICP.'.($group->name ?? $term->id),
        ];
    }

    /**
     * Every class date of the student's current-term plans, for their Google Calendar
     * (ClassCalendarSync): the session details of its plan, plus whether the date is cancelled.
     *
     * @return array{student:array, group:string, term:string, venue:array, dates:array}|null
     */
    public function classDates(int $studentId): ?array
    {
        $loaded = $this->assignedPlans($studentId);
        if (!$loaded) {
            return null;
        }
        [$student, $term, $plans] = $loaded;

        $people = $this->people($plans->pluck('tutor_id')->merge($plans->pluck('personal_tutor_id')));
        $sessions = $plans->mapWithKeys(fn ($p) => [(int) $p->id => $this->session($p, $people)]);
        $main = $plans->where('class_type', 'Theory')->isNotEmpty() ? $plans->where('class_type', 'Theory') : $plans;
        $group = DB::table('groups')->where('id', (int) $this->mostCommon($main->pluck('group_id')))->value('name');
        $venues = DB::table('venues')->whereIn('id', $plans->pluck('venue_id')->filter()->unique())->get(['id', 'name', 'address'])->keyBy('id');

        $dates = DB::table('plans_date_lists')
            ->whereIn('plan_id', $plans->pluck('id'))
            ->orderBy('date')->orderBy('id')
            ->get(['id', 'plan_id', 'date', 'status', 'deleted_at'])
            ->map(function ($d) use ($sessions, $plans, $venues) {
                $plan = $plans->firstWhere('id', $d->plan_id);
                $venue = $venues[$plan->venue_id ?? 0] ?? null;

                return $sessions[(int) $d->plan_id] + [
                    'plans_date_list_id' => (int) $d->id,
                    'date' => substr((string) $d->date, 0, 10),
                    'cancelled' => $d->deleted_at !== null || in_array($d->status, self::CANCELLED, true),
                    'venue' => $venue ? trim($venue->name.($venue->address ? ', '.$this->address($venue->address) : '')) : null,
                ];
            })->values()->all();

        return [
            'student' => ['id' => (int) $student->id, 'registration_no' => $student->registration_no],
            'group' => (string) $group,
            'term' => $this->termName($term->name),
            'dates' => $dates,
        ];
    }

    /**
     * The student, their current attendance term (the newest term they are assigned to, or the
     * term asked for) and their plans in it.
     */
    private function assignedPlans(int $studentId, ?int $termDeclarationId = null): ?array
    {
        $student = DB::table('students')->where('id', $studentId)->whereNull('deleted_at')->first();
        if (!$student) {
            return null;
        }

        $assigned = DB::table('assigns as a')
            ->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->where('a.student_id', $studentId)
            ->whereNull('a.deleted_at')->whereNull('p.deleted_at');

        $term = DB::table('term_declarations')
            ->whereIn('id', (clone $assigned)->whereNotNull('p.term_declaration_id')->pluck('p.term_declaration_id'))
            ->when($termDeclarationId, fn ($q) => $q->where('id', $termDeclarationId))
            ->orderByDesc('start_date')->orderByDesc('id')
            ->first();
        if (!$term) {
            return null;
        }

        $plans = (clone $assigned)
            ->leftJoin('module_creations as mc', 'mc.id', '=', 'p.module_creation_id')
            ->leftJoin('rooms as r', 'r.id', '=', 'p.rooms_id')
            ->where('p.term_declaration_id', $term->id)
            ->select('p.*', 'mc.module_name', 'mc.code as module_code', 'mc.unit_value', 'mc.submission_date as module_submission_date', 'r.name as room_name')
            ->orderBy('p.start_time')->orderBy('p.id')
            ->get();

        return $plans->isEmpty() ? null : [$student, $term, $plans];
    }

    /** One plan as a timetable entry. */
    private function session(object $plan, array $people): array
    {
        $kind = self::KINDS[$plan->class_type] ?? ($plan->class_type ?: 'Class');
        // a URL has no whitespace: links pasted from the old Word routine carry line breaks ("npf-k\r\nkma-ntv")
        $link = (string) preg_replace('/\s+/', '', (string) $plan->virtual_room);
        $link = preg_match('#^https?://#i', $link) ? $link : null;
        $online = preg_match('/online/i', (string) $plan->room_name) || (!$plan->rooms_id && $link);
        $personId = $kind === 'Lecture' ? ($plan->tutor_id ?: $plan->personal_tutor_id) : ($plan->personal_tutor_id ?: $plan->tutor_id);

        return [
            'plan_id' => (int) $plan->id,
            'kind' => $kind,
            'mode' => $online ? 'online' : 'campus',
            // a module with no code (e.g. "GROUP TUTORIAL") is the cross-unit workshop
            'title' => trim((string) $plan->module_code) === '' && $kind !== 'Lecture' ? 'All units' : $this->moduleName($plan->module_name),
            'room' => $online ? null : ($plan->room_name ?: null),
            'link' => $online ? $link : null,
            'person_label' => $kind === 'Lecture' ? 'Lecturer' : 'Tutor',
            'person' => $personId ? ($people[$personId] ?? null) : null,
            'start' => substr((string) $plan->start_time, 0, 5),
            'end' => substr((string) $plan->end_time, 0, 5),
            'days' => array_values(array_filter(array_keys(self::DAYS), fn ($d) => (int) $plan->{$d} === 1)),
        ];
    }

    /**
     * Monday to Saturday (Sunday only when there is a class): each day's sessions by slot, or
     * "support" (a weekday with no class: independent study and tutor-led support) or "study"
     * (Saturday with no class).
     */
    private function days(Collection $sessions, array $slots): array
    {
        $days = [];
        foreach (self::DAYS as $key => $name) {
            $today = $sessions->filter(fn ($s) => in_array($key, $s['days'], true));
            if ($today->isEmpty()) {
                if ($key === 'sun') {
                    continue;
                }
                $days[$name] = ['type' => $key === 'sat' ? 'study' : 'support', 'cells' => []];
                continue;
            }
            $cells = [];
            foreach ($slots as $i => $slot) {
                $cells[$i] = $today->filter(fn ($s) => $s['start'] === $slot['start'] && $s['end'] === $slot['end'])->values()->all();
            }
            $days[$name] = ['type' => 'classes', 'cells' => $cells];
        }

        return $days;
    }

    /** Teaching weeks from the first teaching Monday, ticked where a class of that kind falls. */
    private function weeks(Collection $plans, ?object $instanceTerm, object $term, ?string $teachingStart): array
    {
        if (!$teachingStart) {
            return [];
        }
        $start = Carbon::parse($teachingStart)->startOfWeek(Carbon::MONDAY);
        $dates = DB::table('plans_date_lists')
            ->whereIn('plan_id', $plans->pluck('id'))
            ->whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('status')->orWhereNotIn('status', self::CANCELLED))
            ->get(['plan_id', 'date']);
        $kindOf = $plans->mapWithKeys(fn ($p) => [$p->id => self::KINDS[$p->class_type] ?? 'Class']);

        $count = (int) ($instanceTerm->total_teaching_weeks ?? $term->total_teaching_weeks ?? 0);
        $last = $dates->max('date');
        if ($last) {
            $count = max($count, intdiv($start->diffInDays(Carbon::parse($last)), 7) + 1);
        }
        $revisionStart = $instanceTerm->revision_start_date ?? $term->revision_start_date;
        $revisionEnd = $instanceTerm->revision_end_date ?? $term->revision_end_date;

        $weeks = [];
        for ($n = 1; $n <= min($count, 20); $n++) {
            $monday = $start->copy()->addWeeks($n - 1);
            $sunday = $monday->copy()->addDays(6);
            $inWeek = $dates->filter(fn ($d) => Carbon::parse($d->date)->betweenIncluded($monday, $sunday));
            $kinds = $inWeek->map(fn ($d) => $kindOf[$d->plan_id] ?? 'Class')->unique();
            $weeks[] = [
                'n' => $n,
                'commencing' => $monday->toDateString(),
                'lecture' => $kinds->contains('Lecture'),
                'workshop' => $kinds->contains('Workshop'),
                'tutorial' => $kinds->contains('Tutorial'),
                'revision' => $revisionStart && $revisionEnd
                    && Carbon::parse($revisionStart)->lte($sunday) && Carbon::parse($revisionEnd)->gte($monday),
            ];
        }

        return $weeks;
    }

    /**
     * Every week of the term with its real class dates (plans_date_lists), Monday to Saturday
     * (Sunday only when a class falls on it): each day's classes in time order, a cancelled date
     * kept and marked so the student sees it is off.
     */
    private function calendar(Collection $plans, Collection $sessions, array $weeks): array
    {
        if (!$weeks) {
            return [];
        }
        $dates = DB::table('plans_date_lists')
            ->whereIn('plan_id', $plans->pluck('id'))
            ->get(['plan_id', 'date', 'status', 'deleted_at'])
            ->map(fn ($d) => [
                'plan_id' => (int) $d->plan_id,
                'date' => substr((string) $d->date, 0, 10),
                'cancelled' => $d->deleted_at !== null || in_array($d->status, self::CANCELLED, true),
            ])
            ->filter(fn ($d) => isset($sessions[$d['plan_id']]))
            // one row per class and date: a live row wins over a cancelled duplicate
            ->sortBy(fn ($d) => $d['cancelled'] ? 1 : 0)->unique(fn ($d) => $d['plan_id'].'|'.$d['date'])
            ->groupBy('date');

        $calendar = [];
        foreach ($weeks as $week) {
            $monday = Carbon::parse($week['commencing']);
            $days = [];
            foreach (array_keys(self::DAYS) as $offset => $key) {
                $date = $monday->copy()->addDays($offset);
                $classes = collect($dates[$date->toDateString()] ?? [])
                    ->map(fn ($d) => $sessions[$d['plan_id']] + ['cancelled' => $d['cancelled']])
                    ->sortBy(fn ($c) => $c['start'].$c['end'].$c['plan_id'])->values()->all();
                if ($key === 'sun' && !$classes) {
                    continue;
                }
                $days[] = [
                    'key' => $key,
                    'name' => self::DAYS[$key],
                    'date' => $date->toDateString(),
                    'classes' => $classes,
                    'type' => $classes ? 'classes' : ($key === 'sat' || $key === 'sun' ? 'study' : 'support'),
                ];
            }
            $live = collect($days)->flatMap(fn ($d) => $d['classes'])->where('cancelled', false);
            $calendar[] = $week + ['days' => $days, 'live_classes' => $live->count()];
        }

        return $calendar;
    }

    /** Where a plan meets: "Barking-Red-Room" → "Red Room", and its venue's address, building and town. */
    private function place(object $plan, ?object $venue): array
    {
        $address = $venue ? $this->address((string) $venue->address) : '';
        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));
        $postcodeAt = null;
        foreach ($parts as $i => $part) {
            if (preg_match('/^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i', $part)) {
                $postcodeAt = $i;
            }
        }
        $town = $postcodeAt !== null && $postcodeAt > 0 ? $parts[$postcodeAt - 1] : ($parts ? end($parts) : '');
        $building = $parts ? trim((string) preg_replace('/\s+\d+[A-Za-z]?$/', '', $parts[0])) : '';

        $room = trim(str_replace(['-', '_'], ' ', (string) $plan->room_name));
        if ($town !== '' && stripos($room, $town.' ') === 0) {
            $room = trim(substr($room, strlen($town))); // the town in front of the room's own name
        }

        return [
            'room_label' => $room !== '' && !preg_match('/^online$/i', $room) ? ucwords($room) : null,
            'address' => $address !== '' ? $address : null,
            'building' => $building !== '' ? $building : null,
            'town' => $town !== '' ? $town : null,
        ];
    }

    /** "Kings House, Barking" for the "come to college" line. */
    private function campusPlace(?string $building, ?string $town): ?string
    {
        $place = implode(', ', array_filter([$building, $town]));

        return $place !== '' ? $place : null;
    }

    /** The gap between one slot's end and the next one's start, when every gap is the same. */
    private function breakMinutes(array $slots): ?int
    {
        $gaps = [];
        for ($i = 1; $i < count($slots); $i++) {
            $gaps[] = Carbon::parse($slots[$i - 1]['end'])->diffInMinutes(Carbon::parse($slots[$i]['start']), false);
        }
        $gaps = array_unique($gaps);

        return count($gaps) === 1 && $gaps[0] > 0 ? (int) $gaps[0] : null;
    }

    /** One deadline per lecture module, soonest first; a module without a date shows "to be confirmed". */
    private function assessments(Collection $lectures, array $people): array
    {
        return $lectures->unique('module_creation_id')->map(fn ($p) => [
            'module' => $this->moduleName($p->module_name),
            'code' => $p->module_code ?: null,
            'lecturer' => $p->tutor_id ? ($people[$p->tutor_id] ?? null) : null,
            'date' => $p->submission_date ?: ($p->module_submission_date ?: null),
        ])->sortBy(fn ($a) => $a['date'] ?? '9999-12-31')->values()->all();
    }

    /** user id → the employee's name (else the user name). */
    private function people(Collection $ids): array
    {
        $ids = $ids->filter()->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return [];
        }

        return DB::table('users as u')
            ->leftJoin('employees as e', 'e.user_id', '=', 'u.id')
            ->whereIn('u.id', $ids)
            ->get(['u.id', 'u.name', 'e.first_name', 'e.last_name'])
            ->mapWithKeys(fn ($row) => [(int) $row->id => $this->personName(trim(trim((string) $row->first_name).' '.trim((string) $row->last_name)) ?: (string) $row->name)])
            ->all();
    }

    /** "SORNALY SORKER" → "Sornaly Sorker"; names typed in mixed case stay as they are. */
    private function personName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));

        return mb_strtoupper($name) === $name ? mb_convert_case(mb_strtolower($name), MB_CASE_TITLE) : $name;
    }

    /** Students assigned to the group's plans this term, leaving out those whose attendance is switched off. */
    private function groupSize(int $groupId, int $termId): int
    {
        return (int) DB::table('assigns as a')
            ->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->where('p.group_id', $groupId)->where('p.term_declaration_id', $termId)
            ->whereNull('a.deleted_at')->whereNull('p.deleted_at')
            ->where(fn ($q) => $q->whereNull('a.attendance')->orWhere('a.attendance', '!=', 0))
            ->distinct()->count('a.student_id');
    }

    /** "Y1T1": the instance's place among its course creation's instances, and the session term. */
    private function yearTerm(?object $instanceTerm): ?string
    {
        if (!$instanceTerm || !$instanceTerm->session_term) {
            return null;
        }
        $instance = DB::table('course_creation_instances')->where('id', $instanceTerm->course_creation_instance_id)->first();
        if (!$instance) {
            return 'T'.$instanceTerm->session_term;
        }
        $year = DB::table('course_creation_instances')
            ->where('course_creation_id', $instance->course_creation_id)->whereNull('deleted_at')
            ->where('start_date', '<=', $instance->start_date)
            ->count();

        return 'Y'.max(1, $year).'T'.$instanceTerm->session_term;
    }

    /** "2026 September" → "September 2026". */
    private function termName(?string $name): string
    {
        $name = trim((string) $name);

        return preg_match('/^(\d{4})\s+([A-Za-z]+)$/', $name, $m) ? ucfirst(strtolower($m[2])).' '.$m[1] : $name;
    }

    /** "HND In Hospitality Management" → "HND in Hospitality Management". */
    private function courseName(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];
        foreach ($words as $i => $word) {
            if ($i > 0 && in_array(strtolower($word), ['in', 'of', 'and', 'for', 'the', 'with'], true)) {
                $words[$i] = strtolower($word);
            }
        }

        return implode(' ', $words);
    }

    /** "Managing Food & Beverage Operations (RQF 2025)" → without the specification tag. */
    private function moduleName(?string $name): string
    {
        return trim((string) preg_replace('/\s*\((?:RQF|QCF)[^)]*\)\s*$/i', '', (string) $name));
    }

    /** "PEARSON" → "Pearson"; short acronyms (e.g. "OTHM") stay as entered. */
    private function awardingBody(?string $name): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        return mb_strlen($name) > 4 && mb_strtoupper($name) === $name ? ucwords(mb_strtolower($name)) : $name;
    }

    /** "KINGS HOUSE 96, NORTH STREET, BARKING, IG11 8FH" → title case, the postcode left alone. */
    private function address(string $address): string
    {
        $parts = array_filter(array_map('trim', explode(',', $address)), fn ($p) => $p !== '');

        return implode(', ', array_map(fn ($p) => preg_match('/^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i', $p) ? strtoupper($p) : ucwords(strtolower($p)), $parts));
    }

    private function mostCommon(Collection $values)
    {
        $values = $values->filter(fn ($v) => $v !== null && $v !== '');
        if ($values->isEmpty()) {
            return null;
        }

        return $values->countBy()->sortDesc()->keys()->first();
    }
}
