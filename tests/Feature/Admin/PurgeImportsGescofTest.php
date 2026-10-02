<?php

namespace Tests\Feature\Admin;

use App\Models\GescofImport;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PurgeImportsGescofTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function fichier(string $chemin, int $ageJours): void
    {
        Storage::put($chemin, 'Nom;Prenom');
        touch(Storage::path($chemin), now()->subDays($ageJours)->getTimestamp());
    }

    private function simulation(string $chemin): GescofImport
    {
        return GescofImport::create(['fichier_nom' => basename($chemin), 'fichier_path' => $chemin, 'applique' => false]);
    }

    public function test_un_fichier_non_applique_de_plus_de_sept_jours_est_supprime(): void
    {
        $this->fichier('gescof/ancien.csv', 8);
        $this->fichier('gescof/recent.csv', 6);
        $ancien = $this->simulation('gescof/ancien.csv');
        $recent = $this->simulation('gescof/recent.csv');

        $this->artisan('edl:purge-imports')->expectsOutputToContain('1 fichier(s)')->assertSuccessful();

        Storage::assertMissing('gescof/ancien.csv');
        Storage::assertExists('gescof/recent.csv');
        $this->assertNull($ancien->fresh()->fichier_path);
        $this->assertSame('gescof/recent.csv', $recent->fresh()->fichier_path);
    }

    public function test_un_fichier_orphelin_ancien_est_aussi_supprime(): void
    {
        $this->fichier('gescof/orphelin.xlsx', 30);

        $this->artisan('edl:purge-imports')->assertSuccessful();

        Storage::assertMissing('gescof/orphelin.xlsx');
    }

    public function test_la_duree_peut_etre_precisee(): void
    {
        $this->fichier('gescof/deux-jours.csv', 2);

        $this->artisan('edl:purge-imports --jours=1')->assertSuccessful();

        Storage::assertMissing('gescof/deux-jours.csv');
    }

    public function test_une_simulation_purgee_reste_consultable_mais_ne_peut_plus_etre_appliquee(): void
    {
        $this->fichier('gescof/ancien.csv', 10);
        $import = $this->simulation('gescof/ancien.csv');
        $this->artisan('edl:purge-imports')->assertSuccessful();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.imports.show', $import))->assertOk();

        $this->actingAs($admin)->post(route('admin.imports.appliquer', $import))
            ->assertSessionHas('erreur');
        $this->assertFalse($import->fresh()->applique);
    }

    public function test_la_purge_est_planifiee_chaque_jour(): void
    {
        $commandes = collect(app(Schedule::class)->events())->pluck('command')->implode(' ');

        $this->assertStringContainsString('edl:purge-imports', $commandes);
    }
}
