<?php

namespace App\Observers;

use App\Mail\TutorMonitorStatusUpdate;
use App\Models\Plan;
use App\Models\PlansDateList;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Mail;

class PlansDateListObserver  implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the PlansDateList "created" event.
     */
    public function created(PlansDateList $plansDateList): void
    {
        //
    }

    /**
     * Handle the PlansDateList "updated" event.
     */
    public function updated(PlansDateList $plansDateList): void
    {
        if($plansDateList->class_file_upload_found=="No") {
            $plan = Plan::with('tutor','personalTutor')->where('id',$plansDateList->plan_id)->get()->first();
            Mail::to('registry@lcc.ac.uk')->send(new TutorMonitorStatusUpdate($plansDateList,$plan));

        }
    }

    /**
     * Handle the PlansDateList "deleted" event.
     */
    public function deleted(PlansDateList $plansDateList): void
    {
        //
    }

    /**
     * Handle the PlansDateList "restored" event.
     */
    public function restored(PlansDateList $plansDateList): void
    {
        //
    }

    /**
     * Handle the PlansDateList "force deleted" event.
     */
    public function forceDeleted(PlansDateList $plansDateList): void
    {
        //
    }
}
