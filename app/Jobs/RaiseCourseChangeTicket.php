<?php

namespace App\Jobs;

use App\Models\StudentCourseChangeRequest;
use App\Services\CourseChangeTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Second and later attempts at raising a course change ticket.
 *
 * The first attempt happens in the request, so the student normally has their
 * reference before the page reloads. This exists for the times it does not:
 * Operations restarting, a timeout, a network blip. The request is already
 * saved by then, and the idempotency key means a retry after a response that
 * never arrived returns the ticket that was in fact created rather than opening
 * a second one.
 *
 * Backoff is in minutes because the thing being waited for is another
 * application coming back, not a lock being released.
 */
class RaiseCourseChangeTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /** @var array<int, int> */
    public $backoff = [60, 300, 900, 3600];

    public function __construct(public int $requestId)
    {
    }

    public function handle(CourseChangeTicket $tickets): void
    {
        $courseChange = StudentCourseChangeRequest::with('documents')->find($this->requestId);

        if (! $courseChange || $courseChange->ticket_status === 'raised'):
            return;
        endif;

        if (! $tickets->raise($courseChange, $tickets->storedFiles($courseChange))):
            /* Throwing fails the attempt, which is what puts it back on the
               queue under the backoff above; the reason is already on the row. */
            throw new \RuntimeException(
                'Course change request '.$courseChange->id.' could not be raised: '
                    .$courseChange->ticket_failed_reason
            );
        endif;
    }
}
