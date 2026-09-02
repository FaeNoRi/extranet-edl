<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    public function definition(): array
    {
        $categorie = fake()->randomElement(['presentation_structure', 'mes_documents']);

        $type = $categorie === 'presentation_structure'
            ? fake()->randomElement(['Présentation des locaux', "Registre d'accessibilité", 'Catalogue de formations', 'Liste du matériel'])
            : fake()->randomElement(['Convention', "Guide d'animation", "Livret d'accueil", 'Questionnaire évaluation à chaud', 'Questionnaire évaluation à froid']);

        return [
            'nom' => $type,
            'categorie' => $categorie,
            'type_document' => $type,
            'session_formation_id' => null,
            'chemin_fichier' => 'documents/'.fake()->uuid().'.pdf',
            'nom_fichier_original' => fake()->slug(2).'.pdf',
            'taille' => fake()->numberBetween(20_000, 5_000_000),
            'uploader_id' => null,
        ];
    }

    public function structure(): static
    {
        return $this->state(fn () => [
            'categorie' => 'presentation_structure',
            'session_formation_id' => null,
        ]);
    }

    public function mesDocuments(): static
    {
        return $this->state(fn () => ['categorie' => 'mes_documents']);
    }
}
