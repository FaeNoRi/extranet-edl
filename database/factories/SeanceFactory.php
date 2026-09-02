<?php

namespace Database\Factories;

use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeanceFactory extends Factory
{
    public function definition(): array
    {
        $outils = fake()->randomElements(
            ['Livres', 'Magazines', 'Vidéos', 'Sites internet', 'Jeux', 'Autre'],
            fake()->numberBetween(1, 3)
        );

        $objectifs = fake()->randomElements([
            'Identifier et utiliser un vocabulaire adapté au contexte',
            'Utiliser les principales structures grammaticales',
            'Comprendre un court texte et en dégager le sens global',
            'Communiquer en situation socioprofessionnelle',
            'Se faire comprendre avec une prononciation claire',
        ], fake()->numberBetween(1, 3));

        return [
            'session_formation_id' => SessionFormation::factory(),
            'formateur_id' => User::factory()->formateur(),
            'user_id' => null,
            'date' => fake()->dateTimeBetween('-2 months', '+1 month')->format('Y-m-d'),
            'langue' => 'Anglais',
            'objectifs' => $objectifs,
            'contenu' => fake()->paragraph(),
            'outils' => $outils,
            'sources' => fake()->sentence(8),
            'analyse_seance' => fake()->paragraph(),
            'fiche_pdf_path' => null,
        ];
    }

    /** Fiche pédagogique individuelle (FPC). */
    public function pourStagiaire(User $stagiaire): static
    {
        return $this->state(fn () => ['user_id' => $stagiaire->id]);
    }
}
