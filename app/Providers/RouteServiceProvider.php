<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        /*
         * Server-to-server synchronisation.
         *
         * The 60/minute above is sized for a browser and is keyed by IP, so
         * every sync command on the Operations server shares one budget — and
         * an import that fetches a few hundred files one at a time exhausts it
         * in seconds. These routes are already gated by OAuth client
         * credentials and a scope, so the limit here exists to stop a runaway
         * loop, not to police a caller that has already proved who it is.
         */
        RateLimiter::for('sms-sync', function (Request $request) {
            return Limit::perMinute(1200)->by($request->ip());
        });
    }
}
