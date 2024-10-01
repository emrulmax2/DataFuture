<?php

namespace App\Observers;

use App\Mail\StudentAwardingBodyDetailsEmailUpdate;
use App\Models\Student;
use App\Models\StudentAwardingBodyDetails;
use App\Notifications\StudentAwardingBodyDetailsUpdate;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Mail;

class StudentAwardingBodyDetailsObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the StudentAwardingBodyDetails "created" event.
     */
    public function created(StudentAwardingBodyDetails $studentAwardingBodyDetails): void
    {
        //
    }

    /**
     * Handle the StudentAwardingBodyDetails "updated" event.
     */
    public function updated(StudentAwardingBodyDetails $studentAwardingBodyDetails): void
    {
        if($studentAwardingBodyDetails->registration_document_verified=="No") {
            $student = Student::find($studentAwardingBodyDetails->student_id);
            Mail::to('registry@lcc.ac.uk')->send(new StudentAwardingBodyDetailsEmailUpdate($student));
            
        }
    }

    /**
     * Handle the StudentAwardingBodyDetails "deleted" event.
     */
    public function deleted(StudentAwardingBodyDetails $studentAwardingBodyDetails): void
    {
        //
    }

    /**
     * Handle the StudentAwardingBodyDetails "restored" event.
     */
    public function restored(StudentAwardingBodyDetails $studentAwardingBodyDetails): void
    {
        //
    }

    /**
     * Handle the StudentAwardingBodyDetails "force deleted" event.
     */
    public function forceDeleted(StudentAwardingBodyDetails $studentAwardingBodyDetails): void
    {
        //
    }
}
