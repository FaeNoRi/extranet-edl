<?php

namespace Tests\Feature\Formateur;

use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Formulaire de séance : référentiel en accordéon (un module par section) et documents
 * communs de chaque code consultables depuis le formulaire et le dossier de séance.
 */
class ReferentielAccordeonTest extends TestCase
{
    use RefreshDatabase;

    private User $formateur;

    private SessionFormation $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formateur = User::factory()->formateur()->create();
        $this->session = SessionFormation::factory()->op()->create(['formateur_id' => $this->formateur->id]);
    }

    public function test_chaque_module_est_une_section_repliee_par_defaut(): void
    {
        Referentiel::factory()->create(['module' => 'Grammaire', 'code' => 'G-C1']);
        Referentiel::factory()->create(['module' => 'Vocabulaire', 'code' => 'V-C1']);

        $html = $this->actingAs($this->formateur)->get(route('formateur.seances.create', $this->session))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, '<details'));
        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\bopen\b/', $html, 'Aucun module ne doit être ouvert sans case cochée.');
        $this->assertStringContainsString('cochée(s) sur 1', $html);
    }

    public function test_en_modification_les_modules_coches_sont_ouverts(): void
    {
        $coche = Referentiel::factory()->create(['module' => 'Grammaire', 'code' => 'G-C1']);
        Referentiel::factory()->create(['module' => 'Vocabulaire', 'code' => 'V-C1']);
        $seance = Seance::factory()->create(['session_formation_id' => $this->session->id, 'formateur_id' => $this->formateur->id]);
        $seance->referentiels()->attach($coche->id);

        $html = $this->actingAs($this->formateur)->get(route('formateur.seances.edit', $seance))->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<details[^>]*\bopen\b/', $html));
        $this->assertMatchesRegularExpression('/<details[^>]*open[^>]*>.*?Grammaire/s', $html);
    }

    public function test_les_documents_du_code_sont_consultables_depuis_le_formulaire_et_le_dossier(): void
    {
        $entree = Referentiel::factory()->create(['code' => 'G-C1']);
        $document = Ressource::factory()->create(['nom' => 'Fiche pronoms', 'session_formation_id' => null]);
        $entree->ressources()->attach($document->id);

        $this->actingAs($this->formateur)->get(route('formateur.seances.create', $this->session))
            ->assertSee('Fiche pronoms')
            ->assertSee(route('formateur.ressources.download', $document), false);

        $seance = Seance::factory()->create(['session_formation_id' => $this->session->id, 'formateur_id' => $this->formateur->id]);
        $seance->referentiels()->attach($entree->id);

        $this->actingAs($this->formateur)->get(route('formateur.seances.show', $seance))
            ->assertSee(route('formateur.ressources.download', $document), false);

        // Même chose côté administration, avec ses propres routes.
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.seances.edit', $seance))
            ->assertOk()
            ->assertSee(route('admin.ressources.download', $document), false);
    }
}
