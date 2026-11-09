<?php

namespace App\Jobs;

use App\Services\ClassRoutine\ClassCalendarSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Adds a student's class dates to their Google Calendar (ClassCalendarSync). Queued after their
 * class routine is sent; `php artisan class-calendar:sync {student}` runs the same thing by hand.
 */
class SyncStudentClassCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300];
    public int $timeout = 300;

    public function __construct(public int $studentId)
    {
    }

    public function handle(ClassCalendarSync $sync): void
    {
        $result = $sync->sync($this->studentId);
        Log::info('[Class calendar] '.$result['message'], ['student_id' => $this->studentId, 'status' => $result['status']]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('[Class calendar] not updated', ['student_id' => $this->studentId, 'error' => $e->getMessage()]);
    }
}
