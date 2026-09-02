<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Client;
use App\Models\SessionFormation;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use App\Services\Gescof\GescofImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class GescofImportTest extends TestCase
{
    use RefreshDatabase;

    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixture = base_path('tests/Fixtures/gescof/inscriptions.csv');
    }

    private function seedFormateurs(): void
    {
        User::factory()->formateur()->create(['prenom' => 'Christopher', 'nom' => 'LEBON']);
        User::factory()->formateur()->create(['prenom' => 'Juliette', 'nom' => 'MARTIN']);
    }

    public function test_la_simulation_n_ecrit_rien(): void
    {
        $this->seedFormateurs();

        $rapport = app(GescofImporter::class)->simuler($this->fixture);

        $this->assertSame(10, $rapport->lignesLues);
        $this->assertSame(6, $rapport->comptesCrees);
        $this->assertSame(0, User::whereIn('role', ['stagiaire_op', 'stagiaire_fpc'])->count());
        $this->assertSame(0, SessionFormation::count());

        $this->assertDatabaseHas('gescof_imports', ['applique' => false, 'comptes_crees' => 6]);
    }

    public function test_application_cree_comptes_sessions_et_clients(): void
    {
        $this->seedFormateurs();

        $rapport = app(GescofImporter::class)->appliquer($this->fixture);

        $this->assertSame(6, $rapport->comptesCrees);
        $this->assertSame(5, $rapport->sessionsCreees);
        $this->assertSame(4, $rapport->lignesIgnorees);

        $this->assertSame(4, User::where('role', Role::StagiaireOp->value)->count());
        $this->assertSame(2, User::where('role', Role::StagiaireFpc->value)->count());
        $this->assertDatabaseHas('session_formations', ['num_GESCOF' => '260070A', 'code_stage' => 'AN-OP-8-9', 'code_produit' => 'OP', 'langue' => 'Anglais']);
        $this->assertDatabaseHas('session_formations', ['num_GESCOF' => '260350A', 'code_produit' => 'FPC', 'langue' => 'Espagnol']);
        $this->assertDatabaseMissing('session_formations', ['num_GESCOF' => '260114A']); // OP-ST exclu
    }

    public function test_une_adresse_email_genere_plusieurs_logins(): void
    {
        $this->seedFormateurs();
        app(GescofImporter::class)->appliquer($this->fixture);

        $comptes = User::where('email', 'famille.martin@sfr.fr')->get();
        $this->assertCount(3, $comptes);
        $this->assertSame(3, $comptes->pluck('login')->unique()->count());
        $this->assertSame(1, Client::where('nom', 'DUMONT-MARTIN')->count());
    }

    public function test_regles_d_exclusion(): void
    {
        $this->seedFormateurs();
        $rapport = app(GescofImporter::class)->simuler($this->fixture);

        $types = collect($rapport->anomalies)->pluck('type');
        $this->assertTrue($types->contains('hors_perimetre'));      // AN-OP-ST
        $this->assertTrue($types->contains('acces_refuse'));        // AccesPlateforme = Non
        $this->assertTrue($types->contains('participant_absent'));  // A Définir
        $this->assertTrue($types->contains('email_invalide'));      // CHARLET sans e-mail
    }

    public function test_affectation_des_formateurs(): void
    {
        $this->seedFormateurs();
        app(GescofImporter::class)->appliquer($this->fixture);

        $session = SessionFormation::where('num_GESCOF', '260070A')->firstOrFail();
        $this->assertSame('Christopher', $session->formateur->prenom);
        $this->assertTrue($session->formateurs->contains(fn ($f) => $f->nom === 'LEBON'));
        $this->assertStringContainsString('Christopher LEBON', $session->intervenants_import);

        $fpc = SessionFormation::where('num_GESCOF', '260350A')->firstOrFail();
        $this->assertCount(2, $fpc->formateurs);

        // Le suffixe « Thème de la séance » n'est pas pris pour un formateur.
        $rapport = app(GescofImporter::class)->simuler($this->fixture);
        $anomalies = collect($rapport->anomalies)->where('type', 'formateur_non_reconnu');
        $this->assertTrue($anomalies->contains(fn ($a) => str_contains($a['message'], 'Charles MURRAY')));
        $this->assertFalse($anomalies->contains(fn ($a) => str_contains($a['message'], 'Thème')));
    }

    public function test_reimport_ne_duplique_pas_et_marque_les_disparus(): void
    {
        $this->seedFormateurs();
        $importer = app(GescofImporter::class);

        $importer->appliquer($this->fixture);
        $avant = User::count();

        $rapport = $importer->appliquer($this->fixture);
        $this->assertSame($avant, User::count());
        $this->assertSame(0, $rapport->comptesCrees);
        $this->assertSame(5, $rapport->sessionsMaj);

        // Retirer un stagiaire de la session 260070A (qui garde Julie) : il est
        // marqué disparu, pas supprimé.
        $robert = User::where('nom', 'MARTIN')->where('prenom', 'Robert')->firstOrFail();
        $importer->appliquer($this->fixtureSans('MARTIN;Robert;'));

        $session = SessionFormation::where('num_GESCOF', '260070A')->firstOrFail();
        $pivot = $session->stagiaires()->where('users.id', $robert->id)->firstOrFail()->pivot;
        $this->assertNotNull($pivot->disparu_import_at);
        $this->assertNotNull($robert->fresh());

        // Réapparition dans un import ultérieur : réactivé.
        $rapport = $importer->appliquer($this->fixture);
        $this->assertGreaterThanOrEqual(1, $rapport->comptesReactives);
        $this->assertNull(
            $session->stagiaires()->where('users.id', $robert->id)->firstOrFail()->pivot->disparu_import_at
        );
    }

    public function test_envoi_des_liens_d_acces(): void
    {
        Notification::fake();
        $this->seedFormateurs();

        app(GescofImporter::class)->appliquer($this->fixture, envoyerAcces: true);

        Notification::assertSentTo(
            User::where('email', 'famille.martin@sfr.fr')->get(),
            PasswordSetupLink::class,
        );
        // CHARLET n'a pas d'e-mail : aucun lien.
        Notification::assertNotSentTo(
            User::where('nom', 'CHARLET')->get(),
            PasswordSetupLink::class,
        );
    }

    private function fixtureSans(string $prefixeLigne): string
    {
        $lignes = file($this->fixture);
        $filtrees = array_filter($lignes, fn ($l) => ! str_starts_with($l, $prefixeLigne));
        $chemin = sys_get_temp_dir().'/gescof_reduit_'.uniqid().'.csv';
        file_put_contents($chemin, implode('', $filtrees));

        return $chemin;
    }
}
