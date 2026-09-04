<?php

namespace Tests\Feature\Stagiaire;

use App\Models\Document;
use App\Models\Emargement;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EspaceStagiaireTest extends TestCase
{
    use RefreshDatabase;

    private function inscrire(User $stagiaire, SessionFormation $session): void
    {
        $session->stagiaires()->attach($stagiaire->id);
    }

    public function test_tableau_de_bord_affiche_session_formateur_et_documents(): void
    {
        $formateur = User::factory()->formateur()->create(['presentation' => 'Anglophone natif.']);
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id, 'nom' => 'Anglais du lundi']);
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, $session);

        Document::factory()->structure()->create(['nom' => 'Livret d\'accueil général', 'categorie' => 'presentation_structure']);
        Document::factory()->mesDocuments()->create(['nom' => 'Ma convention', 'session_formation_id' => $session->id]);

        $this->actingAs($stagiaire)->get(route('stagiaire.dashboard'))
            ->assertOk()
            ->assertSee('Anglais du lundi')
            ->assertSee($formateur->nom_complet)
            ->assertSee('Anglophone natif.')
            ->assertSee('Livret d\'accueil général')
            ->assertSee('Ma convention');
    }

    public function test_ressources_ne_liste_que_les_seances_realisees(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, $session);

        $passee = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => Carbon::yesterday()]);
        $future = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => Carbon::tomorrow()]);

        $response = $this->actingAs($stagiaire)->get(route('stagiaire.ressources.index'));
        $response->assertOk()
            ->assertSee($passee->date->format('d/m/Y'))
            ->assertDontSee($future->date->format('d/m/Y'));
    }

    public function test_un_stagiaire_fpc_ne_voit_pas_la_fiche_d_un_autre(): void
    {
        $session = SessionFormation::factory()->fpc()->create();
        $moi = User::factory()->stagiaireFpc()->create();
        $autre = User::factory()->stagiaireFpc()->create();
        $this->inscrire($moi, $session);
        $this->inscrire($autre, $session);

        $maSeance = Seance::factory()->create(['session_formation_id' => $session->id, 'user_id' => $moi->id, 'date' => Carbon::yesterday()]);
        $seanceAutre = Seance::factory()->create(['session_formation_id' => $session->id, 'user_id' => $autre->id, 'date' => Carbon::yesterday()]);

        $this->actingAs($moi)->get(route('stagiaire.ressources.show', $maSeance))->assertOk();
        $this->actingAs($moi)->get(route('stagiaire.ressources.show', $seanceAutre))->assertForbidden();
    }

    public function test_le_dossier_ne_montre_que_les_ressources_transmises(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, $session);

        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => Carbon::yesterday()]);
        $transmise = Ressource::factory()->create(['nom' => 'Fiche exercices']);
        $interne = Ressource::factory()->create(['nom' => 'Corrige formateur']);
        $seance->ressources()->attach($transmise->id, ['transmis' => true]);
        $seance->ressources()->attach($interne->id, ['transmis' => false]);

        $this->actingAs($stagiaire)->get(route('stagiaire.ressources.show', $seance))
            ->assertOk()
            ->assertSee('Fiche exercices')
            ->assertDontSee('Corrige formateur');
    }

    public function test_telechargement_refuse_pour_une_autre_session(): void
    {
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, SessionFormation::factory()->op()->create());

        $autreDoc = Document::factory()->mesDocuments()->create([
            'session_formation_id' => SessionFormation::factory()->op()->create()->id,
        ]);

        $this->actingAs($stagiaire)->get(route('stagiaire.documents.download', $autreDoc))->assertForbidden();
    }

    public function test_emargement_reserve_au_distanciel_fpc(): void
    {
        $presentiel = SessionFormation::factory()->fpc()->create(['distanciel' => false]);
        $distanciel = SessionFormation::factory()->distanciel()->create();

        $s1 = User::factory()->stagiaireFpc()->create();
        $s2 = User::factory()->stagiaireFpc()->create();
        $this->inscrire($s1, $presentiel);
        $this->inscrire($s2, $distanciel);

        $seance1 = Seance::factory()->create(['session_formation_id' => $presentiel->id, 'user_id' => $s1->id, 'date' => Carbon::yesterday()]);
        $seance2 = Seance::factory()->create(['session_formation_id' => $distanciel->id, 'user_id' => $s2->id, 'date' => Carbon::yesterday()]);

        $this->actingAs($s1)->post(route('stagiaire.emargement', $seance1))->assertForbidden();

        $this->actingAs($s2)->post(route('stagiaire.emargement', $seance2))->assertRedirect();
        $emargement = Emargement::firstOrFail();
        $this->assertTrue($emargement->present);
        $this->assertNotNull($emargement->signe_at);
    }

    public function test_un_formateur_n_a_pas_acces_a_l_espace_stagiaire(): void
    {
        $this->actingAs(User::factory()->formateur()->create())
            ->get(route('stagiaire.dashboard'))
            ->assertForbidden();
    }
}
