<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::tokensCan([
            'sms.users.sync' => 'Read users for SMS system synchronization.',
            'sms.applicants.read' => 'Read current applicants for external synchronization.',
            'sms.courses.read' => 'Read courses for external synchronization.',
            'sms.academic-years.read' => 'Read academic years for external synchronization.',
            'sms.terms.read' => 'Read terms for external synchronization.',
            'sms.applicants.write' => 'Attach documents and complete tasks for applicants from external systems.',
            'sms.course-modules.read' => 'Read course modules for external synchronization.',
            'sms.departments.read' => 'Read HR departments for external synchronization.',
            'sms.venues.read' => 'Read venues for external synchronization.',
            'sms.rooms.read' => 'Read rooms for external synchronization.',
            'sms.book-locations.read' => 'Read book locations for external synchronization.',
            'sms.library-books.read' => 'Read library books for external synchronization.',
        ]);
        
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
