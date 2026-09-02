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
            'email' => 'admin@edl-plus.test',
            'nom' => 'Admin',
            'prenom' => 'Super',
        ]);

        User::factory()->formateur()->count(5)->create();
        User::factory()->count(20)->create(); // stagiaire_op par défaut
        User::factory()->stagiaireFpc()->count(10)->create();
    }
}
