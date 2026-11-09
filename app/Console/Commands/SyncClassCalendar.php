<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Services\ClassRoutine\ClassCalendarSync;
use Illuminate\Console\Command;
use Throwable;

/**
 * A student's class dates into their Google Calendar, now (the same as the queued
 * SyncStudentClassCalendar job). Off production the events go to the test calendar
 * (CLASS_CALENDAR_REDIRECT_TO, else MAIL_REDIRECT_TO), never the student's.
 *
 *   php artisan class-calendar:sync LCC20260894
 */
class SyncClassCalendar extends Command
{
    protected $signature = 'class-calendar:sync {student : Student id, or registration number (e.g. LCC20260894)}';

    protected $description = 'Add (or update) a student\'s class dates in their Google Calendar';

    public function handle(ClassCalendarSync $sync): int
    {
        $key = trim((string) $this->argument('student'));
        $student = ctype_digit($key) ? Student::find((int) $key) : Student::where('registration_no', $key)->first();
        if (!$student) {
            $this->error('No student with id or registration number "'.$key.'".');

            return self::FAILURE;
        }

        try {
            $result = $sync->sync((int) $student->id);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $result['status'] === 'done' ? $this->info($student->registration_no.': '.$result['message']) : $this->warn($student->registration_no.': '.$result['message']);

        return $result['status'] === 'done' ? self::SUCCESS : self::FAILURE;
    }
}
