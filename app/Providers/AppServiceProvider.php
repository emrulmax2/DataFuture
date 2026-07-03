<?php

namespace App\Providers;

use App\Models\PlansDateList;
use App\Models\Student;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentUser;
use App\Models\UserPrivilege;
use App\Models\VenueIpAddress;
use App\Observers\PlansDateListObserver;
use App\Observers\StudentAwardingBodyDetailsObserver;
use App\Observers\StudentUserObserver;
use App\Services\AttendanceLiveStatsService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Mail\Mailer;
use Arr;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('user.mailer', function ($app, $parameters) {
            $smtp_host = Arr::get ($parameters, 'smtp_host');
            $smtp_port = Arr::get($parameters, 'smtp_port');
            $smtp_username = Arr::get($parameters, 'smtp_username');
            $smtp_password = Arr::get($parameters, 'smtp_password');
            $smtp_encryption = Arr::get($parameters, 'smtp_encryption');
           
            $from_email = Arr::get($parameters, 'from_email');
            $from_name  = Arr::get($parameters, 'from_name');
           
            $from_email = $parameters['from_email'];
            $from_name  = $parameters['from_name'];
          
           config([
                'mail.mailers.tenant' => [
                    'transport' => 'smtp',
                    'host' => $smtp_host,
                    'port' => $smtp_port,
                    'username' => $smtp_username,
                    'password' => $smtp_password,
                    'encryption' => $smtp_encryption,
                ],
            ]);
           
            $mailer = Mail::mailer('tenant');
            $mailer->alwaysFrom($from_email, $from_name);
            $mailer->alwaysReplyTo($from_email, $from_name);
           
            return $mailer;         
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
        PlansDateList::observe(PlansDateListObserver::class);
        StudentAwardingBodyDetails::observe(StudentAwardingBodyDetailsObserver::class);
        StudentUser::observe(StudentUserObserver::class);
        
        Schema::defaultStringLength(191);

        View::composer('layout.top-menu', function ($view) {
            $shared = [
                'home_work' => false,
                'desktop_login' => false,
                'home_work_statistics' => '',
                'venue_ips' => ['62.31.168.43', '79.171.153.100', '149.34.178.243'],
            ];

            if (Auth::check() && isset(Auth::user()->id)) {
                $workHome = UserPrivilege::where('user_id', Auth::user()->id)
                    ->where('category', 'remote_access')
                    ->where('name', 'work_home')
                    ->first();

                $desktopLogin = UserPrivilege::where('user_id', Auth::user()->id)
                    ->where('category', 'remote_access')
                    ->where('name', 'desktop_login')
                    ->first();

                $ips = VenueIpAddress::pluck('ip')->unique()->toArray();

                $shared['home_work'] = isset($workHome->access) && (int) $workHome->access === 1;
                $shared['desktop_login'] = isset($desktopLogin->access) && (int) $desktopLogin->access === 1;
                $shared['home_work_statistics'] = app(AttendanceLiveStatsService::class)->getUserAttendanceLiveStatistics();
                $shared['venue_ips'] = !empty($ips) ? $ips : $shared['venue_ips'];
            }

            $view->with($shared);
        });
        
    }
}
