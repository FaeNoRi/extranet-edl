<?php

namespace Database\Factories;

use App\Models\Seance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmargementFactory extends Factory
{
    public function definition(): array
    {
        $present = fake()->boolean(85);

        return [
            'seance_id' => Seance::factory(),
            'user_id' => User::factory()->stagiaireFpc(),
            'present' => $present,
            'signe_at' => $present ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'signature_path' => null,
            'commentaire' => $present ? null : fake()->randomElement(['Absent excusé', 'Absent non excusé', null]),
        ];
    }
}
