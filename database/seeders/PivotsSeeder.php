<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Database\Seeder;

class PivotsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $referentiels = Referentiel::all();
        $ressources = Ressource::all();
        $documents = Document::all();
        $seances = Seance::all();
        $sessionFormations = SessionFormation::all();

        // session_formation_user : chaque session a entre 3 et 12 stagiaires
        $sessionFormations->each(function (SessionFormation $session) use ($users) {
            $session->users()->attach(
                $users->random(rand(3, 12))->pluck('id')->toArray()
            );
        });

        // seances_session_formation : chaque séance rattachée à 1 ou 2 sessions
        $seances->each(function (Seance $seance) use ($sessionFormations) {
            $seance->sessionFormations()->attach(
                $sessionFormations->random(rand(1, 2))->pluck('id')->toArray()
            );
        });

        // seances_referentiel : chaque séance couvre 1 à 4 entrées du référentiel
        $seances->each(function (Seance $seance) use ($referentiels) {
            $seance->referentiels()->attach(
                $referentiels->random(rand(1, 4))->pluck('id')->toArray()
            );
        });

        // seances_ressources : chaque séance utilise 0 à 3 ressources
        $seances->each(function (Seance $seance) use ($ressources) {
            if ($ressources->isNotEmpty()) {
                $seance->ressources()->attach(
                    $ressources->random(rand(0, 3))->pluck('id')->toArray()
                );
            }
        });

        // referentiel_ressources : chaque référentiel a 0 à 2 ressources associées
        $referentiels->each(function (Referentiel $referentiel) use ($ressources) {
            if ($ressources->isNotEmpty()) {
                $referentiel->ressources()->attach(
                    $ressources->random(rand(0, 2))->pluck('id')->toArray()
                );
            }
        });

        // user_ressources : chaque user consulte 0 à 5 entrées du référentiel
        $users->each(function (User $user) use ($referentiels) {
            $user->referentiels()->attach(
                $referentiels->random(rand(0, 5))->pluck('id')->toArray()
            );
        });

        // user_documents : chaque user a accès à 0 à 3 documents
        $users->each(function (User $user) use ($documents) {
            if ($documents->isNotEmpty()) {
                $user->documents()->attach(
                    $documents->random(rand(0, 3))->pluck('id')->toArray()
                );
            }
        });
    }
}
