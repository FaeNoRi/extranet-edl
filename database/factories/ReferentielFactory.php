<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReferentielFactory extends Factory
{
    public function definition(): array
    {
        $modules = ['Bases', 'Conjugaison', 'Grammaire', 'Prononciation', 'Methodologie', 'Vocabulaire', 'Au Quotidien'];
        $niveaux = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

        return [
            'module' => fake()->randomElement($modules),
            'code' => strtoupper(fake()->unique()->bothify('REF-###??')),
            'contenu' => fake()->paragraphs(3, true),
            'niveaux' => fake()->randomElements($niveaux, fake()->numberBetween(1, 3)),
        ];
    }
}
