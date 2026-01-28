<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessAddressLosa21;

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
        $schedule->command('passportexpiry:cron')->weeklyOn(7, '23:00');
        $schedule->command('visaexpiry:cron')->weeklyOn(7, '23:10');
        $schedule->command('visaexpired:cron')->weeklyOn(7, '23:20');
        $schedule->command('passportexpired:cron')->weeklyOn(7, '23:30');
        $schedule->command('employeeappraisal:cron')->weeklyOn(7, '23:40');

        $schedule->command('employeestatusupdater:cron')->dailyAt('23:30');
        $schedule->command('dailyclassreminder:cron')->everyThirtyMinutes();

        $schedule->command('coursecontentmissingteamnotification:cron')->weeklyOn(7, '23:45');
        $schedule->command('coursecontentmissingtutornotification:cron')->weeklyOn(7, '23:50');

        $schedule->command('studentdue:cron')->dailyAt('05:00');


        $schedule->command('studentbulkemailcreationmailsend:cron')->everyFifteenMinutes();

        // Schedule postcode -> losa_21 updater job daily at 02:00
        $schedule->command('address:process-losa21')
             ->dailyAt('10:56')
             ->withoutOverlapping()
             ->onOneServer();
        

        
        //$schedule->command('coursecontentmissingteamnotification:cron')->everyMinute();
        //$schedule->command('coursecontentmissingtutornotification:cron')->everyMinute();
        // $schedule->command('passportexpiry:cron')->everyMinute();
        // $schedule->command('visaexpiry:cron')->everyMinute();
        // $schedule->command('visaexpired:cron')->everyMinute();
        // $schedule->command('passportexpired:cron')->everyMinute();
        // $schedule->command('employeeappraisal:cron')->everyMinute();
        //$schedule->command('employeestatusupdater:cron')->everyMinute();
        //$schedule->command('studentdue:cron')->everyMinute();
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
