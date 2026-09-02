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
        // L'administrateur a tous les droits.
        Gate::before(function (User $user) {
            return $user->isAdmin() ? true : null;
        });
    }
}
