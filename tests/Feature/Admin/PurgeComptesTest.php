<?php

namespace Tests\Feature\Admin;

use App\Models\SessionFormation;
use App\Models\SessionJour;
use App\Models\User;
use App\Services\PurgeComptesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class PurgeComptesTest extends TestCase
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

    public function test_op_purge_les_sessions_de_l_annee_scolaire_precedente(): void
    {
        Carbon::setTestNow('2026-09-15');

        $ancien = $this->stagiaire('stagiaire_op', $this->sessionTerminee('op', '2026-06-20'));
        $encours = $this->stagiaire('stagiaire_op', $this->sessionTerminee('op', '2026-10-05'));

        $aPurger = app(PurgeComptesService::class)->comptesOpAPurger();

        $this->assertTrue($aPurger->contains($ancien));
        $this->assertFalse($aPurger->contains($encours));

        Carbon::setTestNow();
    }

    public function test_fpc_purge_les_formations_de_n_moins_1(): void
    {
        Carbon::setTestNow('2026-12-31');

        $fini2025 = $this->stagiaire('stagiaire_fpc', $this->sessionTerminee('fpc', '2025-11-10'));
        $fini2026 = $this->stagiaire('stagiaire_fpc', $this->sessionTerminee('fpc', '2026-03-01'));

        $aPurger = app(PurgeComptesService::class)->comptesFpcAPurger();

        $this->assertTrue($aPurger->contains($fini2025));
        $this->assertFalse($aPurger->contains($fini2026));

        Carbon::setTestNow();
    }

    public function test_la_suppression_est_un_soft_delete_journalise(): void
    {
        $user = $this->stagiaire('stagiaire_op', $this->sessionTerminee('op', '2020-01-01'));

        $n = app(PurgeComptesService::class)->supprimer(collect([$user]), 'test');

        $this->assertSame(1, $n);
        $this->assertSoftDeleted($user);
        $this->assertTrue(Activity::where('log_name', 'Purge')->exists());
    }

    public function test_la_commande_en_simulation_n_ecrit_rien(): void
    {
        Carbon::setTestNow('2026-09-15');
        $user = $this->stagiaire('stagiaire_op', $this->sessionTerminee('op', '2026-06-20'));

        $this->artisan('edl:purge-comptes --op')->assertSuccessful();
        $this->assertNull($user->fresh()->deleted_at);

        $this->artisan('edl:purge-comptes --op --appliquer')->assertSuccessful();
        $this->assertSoftDeleted($user);

        Carbon::setTestNow();
    }
}
