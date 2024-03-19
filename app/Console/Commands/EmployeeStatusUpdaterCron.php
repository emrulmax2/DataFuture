<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EmployeeStatusUpdaterCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employeestatusupdater:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Employee Status Based On Ended Date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $theDate = date('Y-m-d');

        $employments = Employment::whereNotNull('ended_on')->whereHas('employee', function($q){
            $q->where('status', 1);
        })->get();
        if($employments->count() > 0):
            foreach($employments as $empt):
                $ended_on = (isset($empt->ended_on) && !empty($empt->ended_on) ? date('Y-m-d', strtotime($empt->ended_on)) : '');
                if(!empty($ended_on)):
                    $expectedEnded = date('Y-m-d', strtotime($ended_on.'+4 days'));
                    if($theDate == $expectedEnded):
                        $employee_id = $empt->employee_id;
                        Employee::where('id', $employee_id)->update(['status' => 0]);

                        if(isset($empt->employee->user_id) && $empt->employee->user_id > 0):
                            User::where('id', $empt->employee->user_id)->update(['active' => 0]);
                        endif;
                    endif;
                endif;
            endforeach;
        endif;
        return 0;
    }
}
