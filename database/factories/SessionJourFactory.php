<?php

namespace Database\Factories;

use App\Models\SessionFormation;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionJourFactory extends Factory
{
    public function definition(): array
    {
        return [
            'session_formation_id' => SessionFormation::factory(),
            'date' => fake()->dateTimeBetween('-1 month', '+3 months')->format('Y-m-d'),
            'actif' => fake()->boolean(85),
            'commentaire' => fn (array $attrs) => $attrs['actif'] ? null : fake()->randomElement(['Férié', 'Vacances scolaires', 'Pas de séance']),
        ];
    }
}
