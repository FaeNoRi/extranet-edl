<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // En production, seul le référentiel (données de référence) est chargé : UserSeeder crée
        // un compte « admin » au mot de passe connu et DemoSeeder de faux stagiaires/formateurs.
        // Le premier administrateur se crée avec : php artisan edl:creer-admin.
        if (app()->isProduction()) {
            $this->call([ReferentielSeeder::class]);

            return;
        }

        $this->call([
            ReferentielSeeder::class,
            UserSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
