<?php

namespace Database\Seeders;

use App\Models\SessionFormation;
use Illuminate\Database\Seeder;

class SessionFormationSeeder extends Seeder
{
    public function run(): void
    {
        SessionFormation::factory()->count(8)->create();
    }
}
