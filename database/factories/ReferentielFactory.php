<?php

namespace Database\Factories;

use App\Models\Referentiel;
use App\Support\NiveauxReferentiel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferentielFactory extends Factory
{
    public function definition(): array
    {
        $modules = Referentiel::MODULES;
        $niveaux = NiveauxReferentiel::cles();

        return [
            'module' => fake()->randomElement($modules),
            'code' => mb_strtoupper(fake()->unique()->bothify('REF-C##')),
            'contenu' => fake()->sentence(6),
            'langue' => null,
            'badges' => [],
            'archive_le' => null,
            'niveaux' => fake()->randomElements($niveaux, fake()->numberBetween(1, 2)),
        ];
    }
}
