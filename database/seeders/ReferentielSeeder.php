<?php

namespace Database\Seeders;

use App\Models\Referentiel;
use Illuminate\Database\Seeder;

class ReferentielSeeder extends Seeder
{
    public function run(): void
    {
        Referentiel::factory()->count(40)->create();
    }
}
