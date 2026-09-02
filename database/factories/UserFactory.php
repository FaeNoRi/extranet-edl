<?php

namespace Database\Factories;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $prenom = fake()->firstName();
        $nom = fake()->lastName();

        return [
            'email' => fake()->safeEmail(),
            'login' => Str::slug($prenom.'.'.$nom, '.').fake()->unique()->numberBetween(1, 99999),
            'password' => 'password',
            'role' => Role::StagiaireOp->value,
            'nom' => mb_strtoupper($nom),
            'prenom' => $prenom,
            'formateur_fpc' => false,
            'formateur_op' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::Admin->value]);
    }

    public function formateur(): static
    {
        return $this->state(fn () => [
            'role' => Role::Formateur->value,
            'presentation' => fake()->sentence(12),
            'formateur_fpc' => fake()->boolean(70),
            'formateur_op' => fake()->boolean(70),
        ])->afterMaking(function ($user) {
            if (! $user->formateur_fpc && ! $user->formateur_op) {
                $user->formateur_op = true;
            }
        });
    }

    public function stagiaireOp(): static
    {
        return $this->state(fn () => ['role' => Role::StagiaireOp->value]);
    }

    public function stagiaireFpc(): static
    {
        return $this->state(fn () => ['role' => Role::StagiaireFpc->value]);
    }
}
