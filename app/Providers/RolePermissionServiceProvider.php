<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Log;

class RolePermissionServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $permissionService = app(PermissionService::class);

        Gate::define('permission', function (User $user, $permissionKey) use ($permissionService) {
            return $permissionService->checkPermission($user->id, $permissionKey);
        });

        Gate::define('permission_any', function (User $user, ...$permissions) use ($permissionService) {
            return $permissionService->checkAnyPermission($user->id, $permissions);
        });

    }
}