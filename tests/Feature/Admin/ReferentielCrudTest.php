<?php

namespace Tests\Feature\Admin;

use App\Models\Referentiel;
use App\Models\Seance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferentielCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_affichage_liste_creation_et_edition(): void
    {
        $entree = Referentiel::factory()->create();

        $this->get(route('admin.referentiel.index'))->assertOk()->assertSee($entree->code);
        $this->get(route('admin.referentiel.index', ['module' => $entree->module]))->assertOk();
        $this->get(route('admin.referentiel.create'))->assertOk();
        $this->get(route('admin.referentiel.edit', $entree))->assertOk()->assertSee($entree->code);
    }

    public function test_creation_d_une_entree(): void
    {
        $this->post(route('admin.referentiel.store'), [
            'module' => 'Vocabulaire',
            'code' => 'V-C99',
            'contenu' => 'Le vocabulaire de la maison',
            'niveaux' => ['A1', 'A2'],
        ])->assertRedirect(route('admin.referentiel.index'));

        $entree = Referentiel::where('code', 'V-C99')->firstOrFail();
        $this->assertSame('Vocabulaire', $entree->module);
        $this->assertSame(['A1', 'A2'], $entree->niveaux);
    }

    public function test_module_doit_faire_partie_de_la_liste_autorisee(): void
    {
        $this->post(route('admin.referentiel.store'), [
            'module' => 'Inconnu',
            'code' => 'X-C1',
            'contenu' => 'Test',
        ])->assertSessionHasErrors('module');

        $this->assertDatabaseMissing('referentiel', ['code' => 'X-C1']);
    }

    public function test_code_unique(): void
    {
        Referentiel::factory()->create(['code' => 'PRIS']);

        $this->post(route('admin.referentiel.store'), [
            'module' => 'Bases', 'code' => 'PRIS', 'contenu' => 'Test',
        ])->assertSessionHasErrors('code');
    }

    public function test_modification(): void
    {
        $entree = Referentiel::factory()->create(['contenu' => 'Ancien contenu']);

        $this->put(route('admin.referentiel.update', $entree), [
            'module' => $entree->module,
            'code' => $entree->code,
            'contenu' => 'Nouveau contenu',
            'niveaux' => ['B1'],
        ])->assertRedirect(route('admin.referentiel.index'));

        $this->assertSame('Nouveau contenu', $entree->fresh()->contenu);
        $this->assertSame(['B1'], $entree->fresh()->niveaux);
    }

    public function test_suppression_impossible_si_utilisee_dans_une_seance(): void
    {
        $entree = Referentiel::factory()->create();
        $seance = Seance::factory()->create();
        $seance->referentiels()->attach($entree);

        $this->delete(route('admin.referentiel.destroy', $entree))
            ->assertSessionHas('erreur');

        $this->assertNotNull($entree->fresh());
    }

    public function test_suppression(): void
    {
        $entree = Referentiel::factory()->create();

        $this->delete(route('admin.referentiel.destroy', $entree))
            ->assertRedirect(route('admin.referentiel.index'));

        $this->assertNull($entree->fresh());
    }
}
