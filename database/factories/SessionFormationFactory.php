<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFormationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'num_GESCOF' => strtoupper(fake()->unique()->bothify('GESCOF-#####')),
            'nom' => 'Session '.fake()->word().' '.fake()->year(),
            'code_produit' => fake()->randomElement(['FPC', 'OP']),
            'objectifs' => fake()->paragraphs(2, true),
            'distanciel' => fake()->boolean(30),
            'lien_teams' => fake()->boolean(30) ? fake()->url() : null,
            'client' => fake()->company(),
            'dates_planning' => implode(', ', fake()->randomElements(
                array_map(fn () => fake()->date(), range(1, 5)), 3
            )),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
