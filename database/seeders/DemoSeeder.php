<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Document;
use App\Models\Emargement;
use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\SessionJour;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Jeu de données de démonstration cohérent (sessions, séances, ressources,
 * documents, émargements, questionnaires). Ne pas utiliser en production.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $formateurs = User::formateurs()->get();
        $referentiels = Referentiel::all();

        $this->documentsStructure();

        // 8 sessions : moitié FPC, moitié OP, réparties sur les formateurs existants.
        collect(range(1, 8))->each(function (int $i) use ($formateurs, $referentiels) {
            $factory = match (true) {
                $i % 2 === 0 => SessionFormation::factory()->op(),
                $i === 1 => SessionFormation::factory()->fpc()->distanciel(),
                default => SessionFormation::factory()->fpc(),
            };

            $session = $factory->create([
                'client_id' => Client::factory(),
                'formateur_id' => $formateurs->random()->id,
            ]);

            $this->remplirSession($session, $referentiels);
        });
    }

    private function remplirSession(SessionFormation $session, $referentiels): void
    {
        // Stagiaires : rôle selon le code produit, e-mail = e-mail du client
        // (plusieurs logins pour une même adresse).
        $role = $session->isFpc() ? Role::StagiaireFpc : Role::StagiaireOp;
        $stagiaires = User::factory()
            ->count(fake()->numberBetween(4, 10))
            ->state(fn () => [
                'role' => $role->value,
                'email' => $session->client?->email ?? fake()->safeEmail(),
            ])
            ->create();

        $session->stagiaires()->attach($stagiaires->pluck('id'));

        // Planning : une dizaine de jours, quelques-uns désactivés.
        $debut = Carbon::now()->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $date = $debut->copy()->addWeeks($i);
            SessionJour::create([
                'session_formation_id' => $session->id,
                'date' => $date,
                'actif' => ! in_array($i, [3, 7], true),
                'commentaire' => in_array($i, [3, 7], true) ? 'Vacances scolaires' : null,
            ]);
        }

        // Ressources déposées sur la session.
        $ressources = Ressource::factory()
            ->count(fake()->numberBetween(3, 6))
            ->create([
                'uploader_id' => $session->formateur_id,
                'session_formation_id' => $session->id,
            ]);

        // Séances : OP -> séances de groupe ; FPC -> une fiche par stagiaire.
        $nbSeances = fake()->numberBetween(3, 6);

        for ($n = 0; $n < $nbSeances; $n++) {
            $date = $debut->copy()->addWeeks($n)->addDays(1);

            if ($session->isFpc()) {
                foreach ($stagiaires as $stagiaire) {
                    $this->creerSeance($session, $date, $referentiels, $ressources, $stagiaire);
                }
            } else {
                $this->creerSeance($session, $date, $referentiels, $ressources, null);
            }
        }

        // Documents personnalisés de la session.
        Document::factory()->mesDocuments()->count(3)->create([
            'session_formation_id' => $session->id,
        ]);

        // Questionnaire de satisfaction à chaud.
        $this->questionnaireSatisfaction($session);
    }

    private function creerSeance(SessionFormation $session, Carbon $date, $referentiels, $ressources, ?User $stagiaire): void
    {
        $seance = Seance::factory()->create([
            'session_formation_id' => $session->id,
            'formateur_id' => $session->formateur_id,
            'user_id' => $stagiaire?->id,
            'date' => $date,
            'langue' => $session->langue,
        ]);

        $seance->referentiels()->attach(
            $referentiels->random(fake()->numberBetween(1, 3))->pluck('id')
        );

        foreach ($ressources->random(min(3, $ressources->count())) as $ressource) {
            $seance->ressources()->attach($ressource->id, ['transmis' => fake()->boolean(60)]);
        }

        if ($session->distanciel && $stagiaire) {
            Emargement::factory()->create([
                'seance_id' => $seance->id,
                'user_id' => $stagiaire->id,
            ]);
        }
    }

    private function documentsStructure(): void
    {
        foreach ([
            'Présentation des locaux',
            "Registre d'accessibilité",
            'Catalogue de formations',
            'Liste du matériel mis à disposition',
        ] as $nom) {
            Document::factory()->structure()->create(['nom' => $nom, 'type_document' => $nom]);
        }
    }

    private function questionnaireSatisfaction(SessionFormation $session): void
    {
        $q = Questionnaire::create([
            'type' => 'satisfaction_chaud',
            'session_formation_id' => $session->id,
            'titre' => 'Questionnaire de satisfaction à chaud',
            'actif' => true,
        ]);

        QuestionnaireQuestion::insert([
            [
                'questionnaire_id' => $q->id,
                'libelle' => 'Le contenu de la formation a répondu à vos attentes',
                'type' => 'echelle',
                'options' => json_encode(['min' => 1, 'max' => 5]),
                'obligatoire' => true,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'questionnaire_id' => $q->id,
                'libelle' => 'Commentaires libres',
                'type' => 'texte',
                'options' => null,
                'obligatoire' => false,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
