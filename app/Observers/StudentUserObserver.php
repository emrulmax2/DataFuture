<?php

namespace App\Observers;

use App\Mail\StudentTempEmailUpdate;
use App\Models\Student;
use App\Models\StudentUser;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Mail;

class StudentUserObserver  implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the StudentUser "created" event.
     */
    public function created(StudentUser $studentUser): void
    {
        //
    }

    /**
     * Handle the StudentUser "updated" event.
     */
    public function updated(StudentUser $studentUser): void
    {
        if($studentUser->temp_email!="" && !is_null($studentUser->temp_email)) {

           
            $student = Student::where('student_user_id',$studentUser->id)->get()->first();

            Mail::to($studentUser->temp_email)->send(new StudentTempEmailUpdate($student));

        }
    }

    /**
     * Handle the StudentUser "deleted" event.
     */
    public function deleted(StudentUser $studentUser): void
    {
        //
    }

    /**
     * Handle the StudentUser "restored" event.
     */
    public function restored(StudentUser $studentUser): void
    {
        //
    }

    /**
     * Handle the StudentUser "force deleted" event.
     */
    public function forceDeleted(StudentUser $studentUser): void
    {
        //
    }
}
