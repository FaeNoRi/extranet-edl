<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
        // Les requêtes N+1 (chargement paresseux de relations) échouent en dev et en test.
        Model::preventLazyLoading(! $this->app->isProduction());

        // L'administrateur a tous les droits.
        Gate::before(function (User $user) {
            return $user->isAdmin() ? true : null;
        });
    }
}
