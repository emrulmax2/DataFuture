<?php

namespace App\Console\Commands;

use App\Models\Assign;
use App\Models\Plan;
use App\Models\PlansDateList;
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
        $today = '2024-09-16'; //date('Y-m-d');
        $plan_ids = PlansDateList::where('plan_id', 3183)->where('date', $today)->where('status', 'Scheduled')->where('feed_given', '!=', 1)->pluck('plan_id')->unique()->toArray();
        if(!empty($plan_ids) && count($plan_ids) > 0):
            foreach($plan_ids as $plan_id):
                $plan = Plan::with('activeAssign')->find($plan_id);
                $module = (isset($plan->creations->module_name) ? $plan->creations->module_name : '');
                $classDate = date('d-m-Y', strtotime($today));
                $classTime = date('h:i A', strtotime($plan->start_time)).' - '.date('h:i A', strtotime($plan->end_time));
                $room = (isset($plan->room->name) && !empty($plan->room->name) ? $plan->room->name : '');
                if($plan->rooms_id > 0 && $plan->virtual_room == ''):
                    $subject = 'Class Routine for '.$plan->creations->module_name.' on '.$classDate.' at '.$classTime;
                    $message = 'To attend '.$module.' on '.$classDate.' at '.$classTime.', please visit '.$room;
                elseif($plan->rooms_id == 0 && $plan->virtual_room != ''):
                    $subject = 'Virtual Link '.$plan->creations->module_name.' on '.$classDate.' at '.$classTime;
                    $message = 'To attend '.$module.' on '.$classDate.' at '.$classTime.', please visit '.$plan->virtual_room;
                else:
                    $subject = 'Class '.$plan->creations->module_name.' on '.$classDate.' at '.$classTime;
                    $message = 'To attend '.$module.' on '.$classDate.' at '.$classTime.', please visit '.$plan->virtual_room.' or '.$room;
                endif;
                $assigns = $plan->activeAssign;
                if($assigns->count() > 0):
                    /*$smsContent = StudentSmsContent::create([
                        'sms_template_id' => null,
                        'subject' => $subject,
                        'sms' => $message
                    ]);*/
                    $mobileNumbers = [];
                    $i = 1;
                    foreach($assigns as $asign):
                        if(isset($asign->student->contact->mobile) && !empty($asign->student->contact->mobile)):
                            $mobileNumbers[$i] = '07931926852'; //$asign->student->contact->mobile;

                            /*$studentSms = StudentSms::create([
                                'student_id' => $asign->student_id,
                                'student_sms_content_id' => $smsContent->id,
                                'phone' => $asign->student->contact->mobile,
                                'created_by' => auth()->user()->id,
                            ]);*/

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
