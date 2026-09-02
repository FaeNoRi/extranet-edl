<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->sentence(3),
            'chemin_fichier' => 'documents/'.fake()->uuid().'.pdf',
            'nom_fichier_original' => fake()->word().'.pdf',
        ];
    }
}
