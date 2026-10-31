<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Gescof\GescofImporter;
use App\Services\PreflightService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase de test avec un export GESCOF réel : aucun e-mail ne doit partir vers un stagiaire tant
 * que EDL_ENVOIS_STAGIAIRES n'est pas activé. Envois réels (transport « array »), sans
 * Notification::fake(), pour vérifier ce qui sortirait vraiment.
 */
class VerrouEnvoisStagiairesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mail.default' => 'array']);
    }

    /** @return array<int, string> destinataires des e-mails réellement produits */
    private function destinataires(): array
    {
        return collect(app('mailer')->getSymfonyTransport()->messages())
            ->flatMap(fn ($m) => collect($m->getOriginalMessage()->getTo())->map->getAddress())
            ->all();
    }

    public function test_le_verrou_est_actif_par_defaut(): void
    {
        $this->assertFalse(config('edl.envois.stagiaires'));
    }

    public function test_mot_de_passe_oublie_ne_part_pas_vers_un_stagiaire_mais_part_vers_un_formateur(): void
    {
        $stagiaire = User::factory()->stagiaireOp()->create(['email' => 'stagiaire@example.test']);
        $formateur = User::factory()->formateur()->create(['email' => 'formateur@example.test']);

        $this->post(route('password.email'), ['login' => $stagiaire->login])->assertSessionHas('status');
        $this->post(route('password.email'), ['login' => $formateur->login]);

        $this->assertSame(['formateur@example.test'], $this->destinataires());
    }

    public function test_la_commande_d_acces_ne_part_pas_vers_un_stagiaire(): void
    {
        $stagiaire = User::factory()->stagiaireFpc()->create(['email' => 'stagiaire@example.test']);

        $this->artisan('edl:acces', ['login' => $stagiaire->login])
            ->expectsOutputToContain('Envoi bloqué')
            ->doesntExpectOutputToContain('Lien envoyé')
            ->assertSuccessful();

        $this->assertSame([], $this->destinataires());
    }

    public function test_l_import_avec_envoi_demande_ne_produit_aucun_email(): void
    {
        $rapport = app(GescofImporter::class)->appliquer(base_path('tests/Fixtures/gescof/inscriptions.csv'), null, true);

        $this->assertGreaterThan(0, $rapport->comptesCrees, 'Le fichier de test doit créer des comptes.');
        $this->assertSame([], $this->destinataires());
    }

    public function test_une_fois_le_verrou_leve_les_stagiaires_recoivent_leur_lien(): void
    {
        config(['edl.envois.stagiaires' => true]);
        $stagiaire = User::factory()->stagiaireOp()->create(['email' => 'stagiaire@example.test']);

        $this->post(route('password.email'), ['login' => $stagiaire->login]);

        $this->assertSame(['stagiaire@example.test'], $this->destinataires());
    }

    public function test_l_ecran_d_import_le_signale_et_le_preflight_aussi(): void
    {
        $statuts = collect(app(PreflightService::class)->controles())->pluck('statut', 'nom');
        $this->assertSame(PreflightService::AVERTISSEMENT, $statuts['Envois aux stagiaires activés']);

        config(['edl.envois.stagiaires' => true]);
        $statuts = collect(app(PreflightService::class)->controles())->pluck('statut', 'nom');
        $this->assertSame(PreflightService::OK, $statuts['Envois aux stagiaires activés']);
    }
}
