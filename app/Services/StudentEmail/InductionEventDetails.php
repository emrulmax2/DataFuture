<?php

namespace App\Services\StudentEmail;

use Carbon\Carbon;

/**
 * What the induction invitation email shows, taken from the Gather event behind the
 * course's booking link (OperationsGatherClient; user, 30 Oct 2026).
 *
 * The sessions (date, start, end) are structured in Gather. Which courses each session is
 * for is not — staff write it into the event description, as on the live September 2026
 * form:
 *
 *     Venue: Avicenna House, 258–262 Romford Road, London E7 9HZ
 *
 *     1st Induction – 9:30 AM to 1:00 PM
 *     HND in Business (Entrepreneurship and Small Business Management)
 *
 *     2nd Induction – 2:00 PM to 5:30 PM
 *     HND in Hospitality Management
 *
 * so a paragraph whose first line names a session's start time gives that session's
 * courses, and a "Venue:" line gives the venue (else the event's venue field). Sessions
 * that have started are left out; full ones are kept and marked.
 */
class InductionEventDetails
{
    /**
     * @return array{title:string, booking_url:string, bookable:bool, venue_name:string, venue_address:string,
     *   dates:string[], date_label:string, same_day:bool,
     *   sessions:array<int, array{date:string, time:string, courses:string[], sold_out:bool}>}
     */
    public static function fromEvent(array $event, ?Carbon $now = null): array
    {
        $now = $now ?: now();
        $text = str_replace(["\r\n", "\r"], "\n", (string) ($event['description_text'] ?? strip_tags((string) ($event['description'] ?? ''))));
        $blocks = array_values(array_filter(array_map(
            fn ($block) => array_values(array_filter(array_map('trim', explode("\n", $block)), fn ($line) => $line !== '')),
            preg_split("/\n\s*\n/", $text) ?: []
        )));

        $sessions = [];
        foreach ((array) ($event['sessions'] ?? []) as $session) {
            if (empty($session['date'])) {
                continue;
            }
            $start = !empty($session['start_time']) ? Carbon::parse($session['date'].' '.$session['start_time']) : Carbon::parse($session['date'])->endOfDay();
            if (!empty($session['started']) || $start->lt($now)) {
                continue; // already started: nothing to book
            }
            $sessions[] = [
                'date' => Carbon::parse($session['date'])->format('l, j F Y'),
                'time' => self::timeRange($session['start_time'] ?? null, $session['end_time'] ?? null),
                'courses' => self::coursesFor($blocks, $session['start_time'] ?? null),
                'sold_out' => !empty($session['sold_out']),
                'sort' => $start->getTimestamp(),
            ];
        }
        usort($sessions, fn ($a, $b) => $a['sort'] <=> $b['sort']);
        $sessions = array_map(function ($s) {
            unset($s['sort']);

            return $s;
        }, $sessions);

        $dates = array_values(array_unique(array_column($sessions, 'date')));
        [$venueName, $venueAddress] = self::venue($text, (string) ($event['venue'] ?? ''));

        return [
            'title' => (string) ($event['title'] ?? 'Induction'),
            'booking_url' => (string) ($event['booking_url'] ?? ''),
            'bookable' => !empty($event['bookable']),
            'venue_name' => $venueName,
            'venue_address' => $venueAddress,
            'dates' => $dates,
            'date_label' => implode(' and ', $dates),
            'same_day' => count($dates) <= 1,
            'sessions' => $sessions,
        ];
    }

    /** "9:30 am – 1:00 pm", or just the start. */
    public static function timeRange(?string $start, ?string $end): string
    {
        $format = fn ($time) => $time ? Carbon::createFromFormat(strlen($time) > 5 ? 'H:i:s' : 'H:i', $time)->format('g:i a') : '';
        $from = $format($start);
        $to = $format($end);

        return $from !== '' && $to !== '' ? $from.' – '.$to : $from;
    }

    /** The lines under the paragraph heading that names this start time ("1st Induction – 9:30 AM to …"). */
    private static function coursesFor(array $blocks, ?string $startTime): array
    {
        if (!$startTime) {
            return [];
        }
        $time = Carbon::createFromFormat(strlen($startTime) > 5 ? 'H:i:s' : 'H:i', $startTime);
        $wanted = (int) $time->format('G') * 60 + (int) $time->format('i');

        foreach ($blocks as $lines) {
            // the heading's first time is the session's start: "… – 9:30 AM to 1:00 PM", "14:00-17:30"
            if (!preg_match('/(?<![0-9])([0-9]{1,2})[:.]([0-9]{2})(?![0-9])\s*([ap])?\.?\s*m?\b/i', $lines[0] ?? '', $m)) {
                continue;
            }
            $hour = (int) $m[1];
            $meridiem = strtolower($m[3] ?? '');
            if ($meridiem === 'p' && $hour < 12) {
                $hour += 12;
            } elseif ($meridiem === 'a' && $hour === 12) {
                $hour = 0;
            }
            if ($hour * 60 + (int) $m[2] === $wanted) {
                return array_values(array_filter(array_slice($lines, 1), fn ($line) => !preg_match('/^(please|note|venue)\b/i', $line)));
            }
        }

        return [];
    }

    /** ["Avicenna House", "258–262 Romford Road, London E7 9HZ"] from the "Venue:" line, else the venue field. */
    private static function venue(string $text, string $field): array
    {
        $venue = preg_match('/^\s*venue\s*[:–-]\s*(.+)$/imu', $text, $m) ? trim($m[1]) : trim($field);
        if ($venue === '') {
            return ['', ''];
        }
        $parts = array_map('trim', explode(',', $venue, 2));

        return [$parts[0], $parts[1] ?? ''];
    }
}
