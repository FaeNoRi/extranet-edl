<?php

namespace Tests\Feature\Formateur;

use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use App\Support\OptionsSeance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeanceDistancielTest extends TestCase
{
    use RefreshDatabase;

    private User $formateur;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->formateur = User::factory()->formateur()->create();
    }

    private function payload(SessionFormation $session, array $extra = []): array
    {
        return array_merge([
            'session_formation_id' => $session->id,
            'date' => '2026-10-15',
            'objectifs' => [OptionsSeance::OBJECTIFS[0]],
            'contenu' => 'Contenu.',
            'outils' => ['Livres'],
            'sources' => 'Sources.',
            'analyse_seance' => 'Analyse.',
        ], $extra);
    }

    public function test_la_case_distanciel_est_enregistree_et_modifiable(): void
    {
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $this->formateur->id]);

        $this->actingAs($this->formateur)
            ->post(route('formateur.seances.store'), $this->payload($session, ['distanciel' => '1']))
            ->assertSessionHasNoErrors();

        $seance = Seance::firstOrFail();
        $this->assertTrue($seance->distanciel);

        // Case décochée = champ absent du formulaire.
        $this->actingAs($this->formateur)
            ->put(route('formateur.seances.update', $seance), $this->payload($session))
            ->assertSessionHasNoErrors();

        $this->assertFalse($seance->fresh()->distanciel);
    }

    public function test_sans_case_cochee_la_seance_est_en_presentiel(): void
    {
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $this->formateur->id]);

        $this->actingAs($this->formateur)->post(route('formateur.seances.store'), $this->payload($session));

        $this->assertFalse(Seance::firstOrFail()->distanciel);
    }

    public function test_la_case_est_pre_cochee_pour_une_session_en_distanciel(): void
    {
        $distanciel = SessionFormation::factory()->distanciel()->create(['formateur_id' => $this->formateur->id]);
        $presentiel = SessionFormation::factory()->op()->create(['formateur_id' => $this->formateur->id]);

        $this->actingAs($this->formateur)->get(route('formateur.seances.create', $distanciel))
            ->assertOk()
            ->assertSee('Séance en distanciel')
            ->assertSeeInOrder(['name="distanciel"', 'checked'], false);

        $html = $this->actingAs($this->formateur)->get(route('formateur.seances.create', $presentiel))->getContent();
        $this->assertDoesNotMatchRegularExpression('/name="distanciel"[^>]*checked/s', $html);
    }

    public function test_la_mention_apparait_sur_la_fiche_et_le_pdf(): void
    {
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $this->formateur->id]);
        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'formateur_id' => $this->formateur->id, 'distanciel' => true]);

        $this->actingAs($this->formateur)->get(route('formateur.seances.show', $seance))->assertSee('En distanciel');

        $seance->load('sessionFormation.client', 'formateur', 'stagiaire', 'referentiels', 'ressources');
        $this->assertStringContainsString('En distanciel', view('pdf.fiche-pedagogique', ['seance' => $seance])->render());

        $seance->distanciel = false;
        $this->assertStringNotContainsString('En distanciel', view('pdf.fiche-pedagogique', ['seance' => $seance])->render());
    }
}
