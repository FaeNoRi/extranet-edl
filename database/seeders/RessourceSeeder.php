<?php

namespace Database\Seeders;

use App\Models\Ressource;
use Illuminate\Database\Seeder;

class RessourceSeeder extends Seeder
{
    public function run(): void
    {
        Ressource::factory()->count(30)->create();
    }
}
