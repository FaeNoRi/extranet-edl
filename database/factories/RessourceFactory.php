<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RessourceFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['audio', 'video', 'pdf', 'image', 'autre']);
        $ext = match ($type) {
            'audio' => 'mp3', 'video' => 'mp4', 'pdf' => 'pdf', 'image' => 'jpg', default => 'zip',
        };

        return [
            'nom' => ucfirst(fake()->words(3, true)),
            'type_fichier' => $type,
            'chemin_fichier' => 'ressources/'.fake()->uuid().'.'.$ext,
            'nom_fichier_original' => fake()->slug(2).'.'.$ext,
            'taille' => fake()->numberBetween(50_000, 25_000_000),
            'nb_telechargement' => fake()->numberBetween(0, 200),
            'uploader_id' => User::factory()->formateur(),
            'session_formation_id' => null,
        ];
    }
}
