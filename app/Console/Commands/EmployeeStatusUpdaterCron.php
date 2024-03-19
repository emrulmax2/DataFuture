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
        $yesterday = Carbon::yesterday();
        $employments = Employment::where('ended_on', $yesterday)->whereHas('employee', function($q){
            $q->where('status', 1);
        })->get();
        if($employments->count() > 0):
            foreach($employments as $empt):
                $employee_id = $empt->employee_id;
                Employee::where('id', $employee_id)->update(['status' => 0]);

                if(isset($empt->employee->user_id) && $empt->employee->user_id > 0):
                    User::where('id', $empt->employee->user_id)->update(['active' => 0]);
                endif;
            endforeach;
        endif;
        return 0;
    }
}
