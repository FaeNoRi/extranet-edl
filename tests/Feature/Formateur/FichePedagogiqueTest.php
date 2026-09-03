<?php

namespace Tests\Feature\Formateur;

use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use App\Support\OptionsSeance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FichePedagogiqueTest extends TestCase
{
    use RefreshDatabase;

    private User $formateur;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->formateur = User::factory()->formateur()->create();
    }

    private function sessionOp(): SessionFormation
    {
        return SessionFormation::factory()->op()->create(['formateur_id' => $this->formateur->id]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'session_formation_id' => null,
            'date' => '2026-03-10',
            'objectifs' => [OptionsSeance::OBJECTIFS[0]],
            'contenu' => 'Révision du présent simple.',
            'outils' => ['Livres', 'Jeux'],
            'sources' => 'Manuel English File, unité 3.',
            'analyse_seance' => 'Bonne participation, quelques hésitations à l\'oral.',
        ], $extra);
    }

    public function test_creation_d_une_fiche_genere_le_pdf(): void
    {
        $session = $this->sessionOp();
        $module = Referentiel::factory()->create();

        $response = $this->actingAs($this->formateur)->post(route('formateur.seances.store'), $this->payload([
            'session_formation_id' => $session->id,
            'referentiels' => [$module->id],
        ]));

        $seance = Seance::firstOrFail();
        $response->assertRedirect(route('formateur.seances.show', $seance));

        $this->assertSame($this->formateur->id, $seance->formateur_id);
        $this->assertNull($seance->user_id); // OP : pas de stagiaire
        $this->assertEqualsCanonicalizing(['Livres', 'Jeux'], $seance->outils);
        $this->assertTrue($seance->referentiels->contains($module));
        $this->assertNotNull($seance->fiche_pdf_path);
        Storage::assertExists($seance->fiche_pdf_path);
    }

    public function test_fpc_exige_un_stagiaire(): void
    {
        $session = SessionFormation::factory()->fpc()->create(['formateur_id' => $this->formateur->id]);

        $this->actingAs($this->formateur)
            ->post(route('formateur.seances.store'), $this->payload(['session_formation_id' => $session->id]))
            ->assertSessionHasErrors('user_id');
    }

    public function test_upload_de_ressources_transmises_et_internes(): void
    {
        $session = $this->sessionOp();

        $this->actingAs($this->formateur)->post(route('formateur.seances.store'), $this->payload([
            'session_formation_id' => $session->id,
            'fichiers_transmis' => [UploadedFile::fake()->create('exercice.pdf', 40, 'application/pdf')],
            'fichiers_internes' => [UploadedFile::fake()->create('corrige.pdf', 40, 'application/pdf')],
        ]));

        $seance = Seance::firstOrFail();
        $this->assertCount(2, $seance->ressources);
        $this->assertCount(1, $seance->ressourcesTransmises);
        $this->assertSame(2, Ressource::where('session_formation_id', $session->id)->count());
        Storage::assertExists($seance->ressources->first()->chemin_fichier);
    }

    public function test_un_formateur_ne_touche_pas_les_seances_d_une_autre_session(): void
    {
        $autre = SessionFormation::factory()->op()->create();
        $seance = Seance::factory()->create(['session_formation_id' => $autre->id]);

        $this->actingAs($this->formateur)->get(route('formateur.seances.show', $seance))->assertForbidden();
        $this->actingAs($this->formateur)
            ->post(route('formateur.seances.store'), $this->payload(['session_formation_id' => $autre->id]))
            ->assertForbidden();
    }

    public function test_telechargement_de_la_fiche_pdf(): void
    {
        $session = $this->sessionOp();
        $this->actingAs($this->formateur)->post(route('formateur.seances.store'), $this->payload(['session_formation_id' => $session->id]));
        $seance = Seance::firstOrFail();

        $response = $this->actingAs($this->formateur)->get(route('formateur.seances.fiche', $seance));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_acces_refuse_aux_non_formateurs(): void
    {
        $session = $this->sessionOp();

        $this->actingAs(User::factory()->stagiaireOp()->create())
            ->get(route('formateur.sessions.show', $session))
            ->assertForbidden();
    }
}
