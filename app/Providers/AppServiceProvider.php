<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Mots de passe choisis par les utilisateurs : 10 caractères minimum, lettres et chiffres.
        Password::defaults(fn () => Password::min(10)->letters()->numbers());

        // Les requêtes N+1 (chargement paresseux de relations) échouent en dev et en test.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Tous les liens générés (e-mails d'accès, redirections) sont en https en production.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // L'administrateur a tous les droits.
        Gate::before(function (User $user) {
            return $user->isAdmin() ? true : null;
        });
    }
}
