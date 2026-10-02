<?php

namespace Tests\Feature\Admin;

use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use App\Support\OptionsSeance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeanceAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_peut_consulter_une_seance(): void
    {
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id]);
        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'formateur_id' => $formateur->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.seances.show', $seance))
            ->assertOk()
            ->assertSee($formateur->nom_complet);
    }

    public function test_admin_peut_modifier_une_seance_sans_en_devenir_le_formateur(): void
    {
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id]);
        $seance = Seance::factory()->create([
            'session_formation_id' => $session->id,
            'formateur_id' => $formateur->id,
            'contenu' => 'Ancien contenu',
        ]);

        $this->actingAs($this->admin)->get(route('admin.seances.edit', $seance))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.seances.update', $seance), [
            'session_formation_id' => $session->id,
            'date' => $seance->date->format('Y-m-d'),
            'objectifs' => [OptionsSeance::OBJECTIFS[0]],
            'contenu' => 'Contenu corrigé par l\'admin',
            'outils' => ['Livres'],
            'sources' => 'Source corrigée',
            'analyse_seance' => 'Analyse corrigée',
        ])->assertRedirect(route('admin.seances.show', $seance));

        $seance->refresh();
        $this->assertSame('Contenu corrigé par l\'admin', $seance->contenu);
        // L'admin ne doit jamais devenir « le formateur » de la séance qu'il corrige.
        $this->assertSame($formateur->id, $seance->formateur_id);
    }

    public function test_admin_peut_telecharger_la_fiche_pdf(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $seance = Seance::factory()->create(['session_formation_id' => $session->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.seances.fiche', $seance));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_admin_peut_telecharger_une_ressource_de_seance(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $seance = Seance::factory()->create(['session_formation_id' => $session->id]);
        Storage::put('sessions/fake/ressource.pdf', 'contenu');
        $ressource = Ressource::factory()->create([
            'session_formation_id' => $session->id,
            'chemin_fichier' => 'sessions/fake/ressource.pdf',
        ]);
        $seance->ressources()->attach($ressource->id, ['transmis' => true]);

        $this->actingAs($this->admin)
            ->get(route('admin.ressources.download', $ressource))
            ->assertOk();
    }

    public function test_admin_peut_supprimer_une_seance(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $seance = Seance::factory()->create(['session_formation_id' => $session->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.seances.destroy', $seance))
            ->assertRedirect(route('admin.sessions.show', $session));

        $this->assertNull($seance->fresh());
    }

    public function test_un_formateur_non_admin_n_accede_pas_aux_routes_admin(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $seance = Seance::factory()->create(['session_formation_id' => $session->id]);

        $this->actingAs(User::factory()->formateur()->create())
            ->get(route('admin.seances.show', $seance))
            ->assertForbidden();
    }

    public function test_la_fiche_de_session_liste_ses_seances(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $seance = Seance::factory()->create(['session_formation_id' => $session->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.sessions.show', $session))
            ->assertOk()
            ->assertSee($seance->date->format('d/m/Y'));
    }
}
