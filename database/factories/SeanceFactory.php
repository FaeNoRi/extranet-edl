<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SeanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('-2 months', '+2 months')->format('Y-m-d'),
            'description' => fake()->optional()->sentence(10),
            'outils' => fake()->randomElement(['Zoom', 'Teams', 'Salle physique', 'Support papier + audio']),
            'analyse_seance' => fake()->paragraph(),
        ];
    }
}
