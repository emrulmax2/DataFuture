<?php

namespace App\Console\Commands;

use App\Models\Assign;
use App\Models\Plan;
use App\Models\PlansDateList;
use App\Models\Status;
use App\Models\StudentSms;
use App\Models\StudentSmsContent;
use Illuminate\Console\Command;
use App\Traits\SendSmsTrait;

class DailyClassReminder extends Command
{
    use SendSmsTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dailyclassreminder:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Daily class reminder to students';
  
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = date('Y-m-d');
        $currentHour = date('H:i:s');
        $tergegedTimeStart = date('H:i', strtotime('+60 minutes', strtotime($currentHour))).':00';
        $tergegedTimeEnd = date('H:i', strtotime('+30 minutes', strtotime($tergegedTimeStart))).':00';
        $plan_ids = PlansDateList::where('date', $today)->where('status', 'Scheduled')->where('feed_given', '!=', 1)->whereHas('plan', function($q) use($tergegedTimeStart, $tergegedTimeEnd){
            $q->where('start_time', '>=', $tergegedTimeStart)->where('start_time', '<', $tergegedTimeEnd);
        })->pluck('plan_id')->unique()->toArray();
        if(!empty($plan_ids) && count($plan_ids) > 0):
            foreach($plan_ids as $plan_id):
                $plan = Plan::with('activeAssign')->find($plan_id);
                $plan_cc_id = (isset($plan->course_creation_id) && $plan->course_creation_id > 0 ? $plan->course_creation_id : 0);

                $module = (isset($plan->creations->module_name) ? $plan->creations->module_name : '');
                $classDate = date('d-m-Y', strtotime($today));
                $classTime = date('h:i A', strtotime($plan->start_time)).' - '.date('h:i A', strtotime($plan->end_time));
                $room = (isset($plan->room->name) && !empty($plan->room->name) ? $plan->room->name : '');
                $virtualRoom = (isset($plan->virtual_room) && !empty($plan->virtual_room) ? $plan->virtual_room : '');
                if($plan->rooms_id > 0 && $virtualRoom == ''):
                    $subject = 'Class Routine for '.$module.' on '.$classDate.' at '.$classTime;
                    $message = 'To attend '.$module.' on '.$classDate.' at '.$classTime.', please visit '.$room;
                elseif($plan->rooms_id == 0 && $virtualRoom != ''):
                    $subject = 'Virtual Link '.$module.' on '.$classDate.' at '.$classTime;
                    $message = 'To attend '.$module.' on '.$classDate.' at '.$classTime.', please visit '.$virtualRoom;
                else:
                    $subject = 'Class '.$module.' on '.$classDate.' at '.$classTime;
                    $message = 'To attend '.$module.' on '.$classDate.' at '.$classTime.', please visit '.$virtualRoom.' or '.$room;
                endif;
                $assigns = $plan->activeAssign;
                if($assigns->count() > 0):
                    $smsContent = StudentSmsContent::create([
                        'sms_template_id' => null,
                        'subject' => $subject,
                        'sms' => $message
                    ]);
                    $mobileNumbers = [];
                    $i = 1;
                    foreach($assigns as $asign):
                        $std_cc_id = (isset($asign->student->activeCR->course_creation_id) && $asign->student->activeCR->course_creation_id > 0 ? $asign->student->activeCR->course_creation_id : 0);
                        if(isset($asign->student->contact->mobile) && !empty($asign->student->contact->mobile) && isset($asign->student->status->active) && $asign->student->status->active == 1 && ($std_cc_id > 0 && $plan_cc_id >= $std_cc_id)):
                            $mobileNumbers[$i] = $asign->student->contact->mobile;

                            $studentSms = StudentSms::create([
                                'student_id' => $asign->student_id,
                                'student_sms_content_id' => $smsContent->id,
                                'phone' => $asign->student->contact->mobile,
                                'created_by' => 1,
                            ]);

                            $i++;
                        endif;
                    endforeach;
                    $this->sendSms($mobileNumbers, $message);
                endif;
            endforeach;
        endif;

        return 0;
    }
}
