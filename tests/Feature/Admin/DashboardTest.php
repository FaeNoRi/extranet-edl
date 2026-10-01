<?php

namespace Tests\Feature\Admin;

use App\Models\Ressource;
use App\Models\SessionFormation;
use App\Models\SessionJour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_vue_d_ensemble_affiche_les_nouvelles_sections(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Alertes')
            ->assertSee('Avancement des sessions FPC en cours')
            ->assertSee('Questionnaires actifs')
            ->assertSee('Dernières ressources importées');
    }

    public function test_la_vue_d_ensemble_liste_les_dernieres_ressources_importees(): void
    {
        Ressource::factory()->create(['nom' => 'Fiche exercices semaine 3']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Fiche exercices semaine 3');
    }

    public function test_l_accordeon_avancement_resume_les_listes_trop_longues_au_lieu_de_tout_lister(): void
    {
        foreach (range(1, 6) as $i) {
            $session = SessionFormation::factory()->fpc()->create();
            SessionJour::factory()->create(['session_formation_id' => $session->id, 'date' => now()->addMonth(), 'actif' => true]);
        }

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('6 session(s) FPC en cours')
            ->assertSee('5 plus en retard affichées');
    }

    public function test_un_admin_peut_supprimer_une_ressource_depuis_le_tableau_de_bord(): void
    {
        Storage::fake('local');
        $ressource = Ressource::factory()->create([
            'session_formation_id' => SessionFormation::factory()->create()->id,
            'chemin_fichier' => 'ressources/test.pdf',
        ]);
        Storage::put($ressource->chemin_fichier, 'contenu');

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.ressources.destroy', $ressource))
            ->assertRedirect();

        $this->assertModelMissing($ressource);
        Storage::assertMissing($ressource->chemin_fichier);
    }
}
