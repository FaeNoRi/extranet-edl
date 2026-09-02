<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ReferentielSeeder::class,
            RessourceSeeder::class,
            DocumentSeeder::class,
            SessionFormationSeeder::class,
            SeanceSeeder::class,
            PivotsSeeder::class, // toujours en dernier
        ]);
    }
}
