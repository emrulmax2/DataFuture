<?php

namespace App\Providers;

use App\Models\ComonSmtp;
use App\Models\EmailTemplate;
use App\Models\PlansDateList;
use App\Models\SmsTemplate;
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
        // Singleton so its per-request caches (venue IPs, per-user decision) are
        // actually reused. remote_access is an appended attribute, so it is read
        // on every User serialisation.
        $this->app->singleton(\App\Services\RemoteAccessService::class);

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

        // Supplies SMTP + template data for the global quick Send-Email / Send-SMS
        // popups so every student profile controller need not pass it explicitly.
        View::composer('pages.students.live.partials.quick-communication-modals', function ($view) {
            $view->with([
                'smtps' => ComonSmtp::all(),
                'emailTemplates' => EmailTemplate::where('live', 1)->where('status', 1)->orderBy('email_title', 'ASC')->get(),
                'smsTemplates' => SmsTemplate::where('live', 1)->where('status', 1)->orderBy('sms_title', 'ASC')->get(),
            ]);
        });

        View::composer('layout.top-menu', function ($view) {
            $shared = [
                'home_work' => false,
                'desktop_login' => false,
                'home_work_statistics' => '',
                'venue_ips' => ['62.31.168.43', '79.171.153.100', '149.34.178.243'],
            ];

            if (Auth::check() && isset(Auth::user()->id)) {
                // Read through priv() so the top menu follows
                // config('privileges.source') rather than the legacy table.
                $priv = Auth::user()->priv();
                $ips = VenueIpAddress::pluck('ip')->unique()->toArray();

                $shared['home_work'] = isset($priv['work_home']) && (int) $priv['work_home'] === 1;
                $shared['desktop_login'] = isset($priv['desktop_login']) && (int) $priv['desktop_login'] === 1;
                $shared['home_work_statistics'] = app(AttendanceLiveStatsService::class)->getUserAttendanceLiveStatistics();
                $shared['venue_ips'] = !empty($ips) ? $ips : $shared['venue_ips'];
            }

            $view->with($shared);
        });
        
    }
}
