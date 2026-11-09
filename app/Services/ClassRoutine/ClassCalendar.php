<?php

namespace App\Services\ClassRoutine;

/**
 * A student's Google Calendar, as far as their class dates go (ClassCalendarSync).
 * GoogleClassCalendar is the real one; tests bind a fake.
 */
interface ClassCalendar
{
    /** Credentials are in place to reach Google. */
    public function configured(): bool;

    /**
     * Create the event, or update it when its id already exists (also brings back one the user deleted).
     *
     * @return string "created" or "updated"
     */
    public function upsert(string $calendarUser, array $event): string;

    /** @return bool false when the event was not there */
    public function delete(string $calendarUser, string $eventId): bool;

    /** Ids of the student's class events from $fromIso on (they carry the private property lcc_student). */
    public function eventIds(string $calendarUser, int $studentId, string $fromIso): array;
}
