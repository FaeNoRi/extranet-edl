<?php

namespace Tests\Feature\Admin;

use App\Models\Questionnaire;
use App\Models\QuestionnaireQuestion;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\SessionJour;
use App\Models\User;
use App\Services\TableauBordAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TableauBordAdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private function stagiaire(string $role, SessionFormation $session): User
    {
        $user = User::factory()->create(['role' => $role]);
        $session->stagiaires()->attach($user->id);

        return $user;
    }

    private function sessionTerminee(string $produit, string $date): SessionFormation
    {
        $session = SessionFormation::factory()->{$produit}()->create();
        SessionJour::factory()->create(['session_formation_id' => $session->id, 'date' => $date]);

        return $session;
    }

    public function test_les_purges_en_attente_sont_comptees(): void
    {
        Carbon::setTestNow('2026-09-15');

        $this->stagiaire('stagiaire_op', $this->sessionTerminee('op', '2026-06-20'));
        $this->stagiaire('stagiaire_fpc', $this->sessionTerminee('fpc', '2025-11-10'));

        $alertes = app(TableauBordAdminService::class)->alertes();

        $this->assertSame(1, $alertes['purgesOp']);
        $this->assertSame(1, $alertes['purgesFpc']);

        Carbon::setTestNow();
    }

    public function test_une_session_fpc_en_cours_sans_seance_recente_est_signalee(): void
    {
        Carbon::setTestNow('2026-09-30');

        $decrochee = SessionFormation::factory()->fpc()->create(['nom' => 'Décrochée']);
        SessionJour::factory()->create(['session_formation_id' => $decrochee->id, 'date' => '2026-10-15', 'actif' => true]);
        Seance::factory()->create(['session_formation_id' => $decrochee->id, 'date' => '2026-08-20']);

        $active = SessionFormation::factory()->fpc()->create(['nom' => 'Active']);
        SessionJour::factory()->create(['session_formation_id' => $active->id, 'date' => '2026-10-15', 'actif' => true]);
        Seance::factory()->create(['session_formation_id' => $active->id, 'date' => '2026-09-25']);

        $terminee = SessionFormation::factory()->fpc()->create(['nom' => 'Terminée']);
        SessionJour::factory()->create(['session_formation_id' => $terminee->id, 'date' => '2026-08-01', 'actif' => true]);

        $decrochees = app(TableauBordAdminService::class)->sessionsFpcSansSeanceRecente(21);

        $this->assertTrue($decrochees->contains('nom', 'Décrochée'));
        $this->assertFalse($decrochees->contains('nom', 'Active'));
        $this->assertFalse($decrochees->contains('nom', 'Terminée'));

        Carbon::setTestNow();
    }

    public function test_avancement_fpc_calcule_le_taux_de_jours_realises(): void
    {
        Carbon::setTestNow('2026-09-30');

        $session = SessionFormation::factory()->fpc()->create();
        foreach (['2026-09-01', '2026-09-08', '2026-09-15', '2026-10-15'] as $date) {
            SessionJour::factory()->create(['session_formation_id' => $session->id, 'date' => $date, 'actif' => true]);
        }
        Seance::factory()->create(['session_formation_id' => $session->id, 'date' => '2026-09-01']);
        Seance::factory()->create(['session_formation_id' => $session->id, 'date' => '2026-09-08']);

        $avancement = app(TableauBordAdminService::class)->avancementSessionsFpc()->first();

        $this->assertSame(2, $avancement['realises']);
        $this->assertSame(4, $avancement['planifies']);
        $this->assertSame(50, $avancement['taux']);

        Carbon::setTestNow();
    }

    public function test_avancement_fpc_ne_tronque_pas_au_niveau_du_service(): void
    {
        // La troncature à l'affichage (les plus en retard d'abord) est de la
        // responsabilité de la vue, pas du service, pour que le total reste
        // disponible pour le résumé.
        foreach (range(1, 6) as $i) {
            $session = SessionFormation::factory()->fpc()->create();
            SessionJour::factory()->create(['session_formation_id' => $session->id, 'date' => now()->addMonth(), 'actif' => true]);
        }

        $this->assertCount(6, app(TableauBordAdminService::class)->avancementSessionsFpc());
    }

    public function test_taux_de_reponse_aux_questionnaires_actifs(): void
    {
        $session = SessionFormation::factory()->fpc()->create();
        $stagiaire1 = $this->stagiaire('stagiaire_fpc', $session);
        $this->stagiaire('stagiaire_fpc', $session);

        $questionnaire = Questionnaire::create([
            'titre' => 'Satisfaction', 'type' => 'satisfaction_chaud',
            'session_formation_id' => $session->id, 'actif' => true,
        ]);
        QuestionnaireQuestion::create(['questionnaire_id' => $questionnaire->id, 'libelle' => 'Note ?', 'type' => 'echelle', 'obligatoire' => true, 'ordre' => 1]);
        $questionnaire->repondants()->attach($stagiaire1->id, ['soumis_at' => now()]);

        $ligne = app(TableauBordAdminService::class)->questionnairesTauxReponse()->first();

        $this->assertSame(1, $ligne['reponses']);
        $this->assertSame(2, $ligne['eligibles']);
        $this->assertSame(50, $ligne['taux']);
    }
}
