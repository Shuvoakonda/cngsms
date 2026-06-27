<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdministrator());
        Gate::define('delete-records', fn (User $user) => $user->canDeleteRecords());
        Gate::define('manage-settings', fn (User $user) => $user->canManageSettings());
        Gate::define('manage-users', fn (User $user) => $user->canManageUsers());

        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user) => $user->hasPermission($permission));
        }

        View::composer([
            'layouts.app',
            'layouts.guest',
            'dashboard',
            'admin.*',
            'purchases.*',
            'payments.*',
            'reports.*',
        ], function ($view) {
            $view->with('company', Company::current());
        });
    }
}
