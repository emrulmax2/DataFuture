<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/** A student's status was changed on the student record (StudentController::studentUpdateStatus). */
class StudentStatusChanged
{
    use Dispatchable;

    public function __construct(
        public int $studentId,
        public ?int $previousStatusId,
        public int $statusId,
    ) {
    }
}
