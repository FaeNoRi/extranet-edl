<?php

namespace Database\Seeders;

use App\Models\Seance;
use Illuminate\Database\Seeder;

class SeanceSeeder extends Seeder
{
    public function run(): void
    {
        Seance::factory()->count(50)->create();
    }
}
