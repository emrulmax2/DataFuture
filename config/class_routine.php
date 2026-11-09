<?php

/*
 * Class routine (ICP): Task Manager › "Send ICP & Mark as Complete" on a task with
 * "Class Routine (ICP)" = Yes (user, 28 Oct 2026). App\Services\ClassRoutine\*.
 */
return [

    /* The letter issued with it ("LCC Class Routine"): its [Link] becomes the routine's
       download link and its "class routine for <term>" the student's current term. Looked
       up by title when this id is missing or renamed on an install. */
    'letter_set_id' => (int) env('CLASS_ROUTINE_LETTER_SET_ID', 113),
    'letter_title' => env('CLASS_ROUTINE_LETTER_TITLE', 'LCC Class Routine'),

    /* Who signs the letter (23 = Dr. Abdur Rahim, Registrar, as on the old letters). */
    'signatory_id' => (int) env('CLASS_ROUTINE_SIGNATORY_ID', 23),

    /* Not stored with the class plans, so set here: the weekday window and weekly hours
       of tutor-led academic support printed on the routine. */
    'support_hours' => env('CLASS_ROUTINE_SUPPORT_HOURS', '10:00 – 16:00'),
    'support_hours_per_week' => (int) env('CLASS_ROUTINE_SUPPORT_HOURS_PER_WEEK', 4),

    'phone' => env('CLASS_ROUTINE_PHONE', '020 7377 1077'),

    /* Text the link to the student: unset = production only (off on a local install,
       user 28 Oct 2026); true / false forces it either way. */
    'sms' => env('CLASS_ROUTINE_SMS'),

    /* The student's class dates in their @lcc.ac.uk Google Calendar, queued after the routine is
       sent (App\Jobs\SyncStudentClassCalendar). Needs the Calendar scope on the Workspace service
       account (domain-wide delegation: https://www.googleapis.com/auth/calendar.events).
       Off production every student's classes go to redirect_to (default MAIL_REDIRECT_TO). */
    'calendar' => [
        'enabled' => (bool) env('CLASS_CALENDAR_ENABLED', true),
        'redirect_to' => env('CLASS_CALENDAR_REDIRECT_TO'),
        'time_zone' => env('CLASS_CALENDAR_TIME_ZONE', 'Europe/London'),
    ],

    /* How long the emailed / texted download link works. */
    'link_days' => (int) env('CLASS_ROUTINE_LINK_DAYS', 365),
];
