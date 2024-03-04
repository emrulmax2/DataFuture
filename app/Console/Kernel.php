<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*$schedule->command('passportexpiry:cron')->weeklyOn(6, '10:00');
        $schedule->command('visaexpiry:cron')->weeklyOn(6, '10:10');
        $schedule->command('visaexpired:cron')->weeklyOn(6, '10:20');
        $schedule->command('passportexpired:cron')->weeklyOn(6, '10:30');
        $schedule->command('employeeappraisal:cron')->weeklyOn(6, '10:40');*/

        $schedule->command('passportexpiry:cron')->everyMinute();
        $schedule->command('visaexpiry:cron')->everyMinute();
        $schedule->command('visaexpired:cron')->everyMinute();
        $schedule->command('passportexpired:cron')->everyMinute();
        $schedule->command('employeeappraisal:cron')->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
