<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
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
        Schema::defaultStringLength(191);
    }
}
