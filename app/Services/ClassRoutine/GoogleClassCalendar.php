<?php

namespace App\Services\ClassRoutine;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Exception as GoogleException;

/**
 * Google Calendar API through the Google Workspace service account the student email job uses
 * (config student_email.google.credentials), acting as the calendar's owner by domain-wide
 * delegation. Needs the scope https://www.googleapis.com/auth/calendar.events added to that
 * service account in the Admin console (Security › API controls › Domain-wide delegation).
 */
class GoogleClassCalendar implements ClassCalendar
{
    public function configured(): bool
    {
        $key = (string) config('student_email.google.credentials');

        return $key !== '' && is_readable($this->path($key));
    }

    public function upsert(string $calendarUser, array $event): string
    {
        $events = $this->service($calendarUser)->events;
        try {
            $events->insert('primary', new Calendar\Event($event));

            return 'created';
        } catch (GoogleException $e) {
            if ((int) $e->getCode() !== 409) { // 409: that id exists (also after the user deleted it)
                throw $this->fail('add an event to '.$calendarUser, $e);
            }
        }
        try {
            $events->update('primary', $event['id'], new Calendar\Event($event));
        } catch (GoogleException $e) {
            throw $this->fail('update an event in '.$calendarUser, $e);
        }

        return 'updated';
    }

    public function delete(string $calendarUser, string $eventId): bool
    {
        try {
            $this->service($calendarUser)->events->delete('primary', $eventId);

            return true;
        } catch (GoogleException $e) {
            if (in_array((int) $e->getCode(), [404, 410], true)) { // never added, or already removed
                return false;
            }
            throw $this->fail('remove an event from '.$calendarUser, $e);
        }
    }

    public function eventIds(string $calendarUser, int $studentId, string $fromIso): array
    {
        $ids = [];
        $page = null;
        try {
            do {
                $list = $this->service($calendarUser)->events->listEvents('primary', array_filter([
                    'privateExtendedProperty' => 'lcc_student='.$studentId,
                    'timeMin' => $fromIso,
                    'maxResults' => 250,
                    'pageToken' => $page,
                ]));
                foreach ($list->getItems() as $item) {
                    $ids[] = $item->getId();
                }
                $page = $list->getNextPageToken();
            } while ($page);
        } catch (GoogleException $e) {
            throw $this->fail('read the calendar of '.$calendarUser, $e);
        }

        return $ids;
    }

    private function service(string $calendarUser): Calendar
    {
        $client = new Client();
        $client->setAuthConfig($this->path((string) config('student_email.google.credentials')));
        $client->setScopes([Calendar::CALENDAR_EVENTS]);
        $client->setSubject($calendarUser);

        return new Calendar($client);
    }

    private function fail(string $what, GoogleException $e): \RuntimeException
    {
        $reason = collect($e->getErrors())->pluck('message')->filter()->implode('; ') ?: $e->getMessage();
        if (str_contains($reason, 'unauthorized_client')) {
            $reason = 'the service account is not allowed the Calendar scope yet (Admin console › Security › API controls › Domain-wide delegation: add https://www.googleapis.com/auth/calendar.events)';
        }

        return new \RuntimeException('Google could not '.$what.': '.$reason, (int) $e->getCode(), $e);
    }

    /** Relative key paths are read from storage/app, as for the student email job. */
    private function path(string $key): string
    {
        return str_starts_with($key, '/') ? $key : storage_path('app/'.ltrim($key, '/'));
    }
}
