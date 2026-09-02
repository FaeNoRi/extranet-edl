<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'login' => 'admin',
            'email' => 'admin@edl-grandcalais.fr',
            'nom' => 'ADMIN',
            'prenom' => 'Super',
        ]);

        User::factory()->formateur()->count(6)->create();
    }
}
