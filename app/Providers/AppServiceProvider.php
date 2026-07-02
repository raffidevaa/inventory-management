<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Admin-only gate: user management, role changes
        Gate::define('manage-users', fn(User $user) => $user->hasRole('admin'));

        // Admin + Staff gate: item and borrowing CRUD
        Gate::define('manage-inventory', fn(User $user) => $user->hasAnyRole(['admin', 'staff']));

        // Admin + Staff gate: processing returns
        Gate::define('process-return', fn(User $user) => $user->hasAnyRole(['admin', 'staff']));

        // All authenticated roles can view reports/dashboard
        Gate::define('view-reports', fn(User $user) => true);
    }
}
