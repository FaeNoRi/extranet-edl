<?php

namespace Database\Factories;

use App\Enums\CodeProduit;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFormationFactory extends Factory
{
    public function definition(): array
    {
        $code = fake()->randomElement([CodeProduit::Fpc->value, CodeProduit::Op->value]);
        $distanciel = $code === CodeProduit::Fpc->value ? fake()->boolean(35) : false;

        return [
            'num_GESCOF' => mb_strtoupper(fake()->unique()->bothify('GESCOF-#####')),
            'nom' => 'Anglais '.fake()->randomElement(['professionnel', 'général', 'technique', 'commercial']),
            'code_produit' => $code,
            'langue' => 'Anglais',
            'client_id' => Client::factory(),
            'formateur_id' => User::factory()->formateur(),
            'objectifs' => $code === CodeProduit::Fpc->value ? fake()->paragraph() : null,
            'distanciel' => $distanciel,
            'lien_teams' => $distanciel ? fake()->url() : null,
            'rythme_op' => $code === CodeProduit::Op->value
                ? fake()->randomElement(['trimestre', 'annee'])
                : null,
            'dates_planning' => collect(range(1, 8))
                ->map(fn () => fake()->dateTimeBetween('-1 month', '+3 months')->format('d/m/Y'))
                ->implode(', '),
        ];
    }

    public function fpc(): static
    {
        return $this->state(fn () => [
            'code_produit' => CodeProduit::Fpc->value,
            'objectifs' => fake()->paragraph(),
            'rythme_op' => null,
        ]);
    }

    public function op(): static
    {
        return $this->state(fn () => [
            'code_produit' => CodeProduit::Op->value,
            'objectifs' => null,
            'distanciel' => false,
            'lien_teams' => null,
            'rythme_op' => fake()->randomElement(['trimestre', 'annee']),
        ]);
    }

    public function distanciel(): static
    {
        return $this->state(fn () => [
            'code_produit' => CodeProduit::Fpc->value,
            'distanciel' => true,
            'lien_teams' => fake()->url(),
        ]);
    }
}
