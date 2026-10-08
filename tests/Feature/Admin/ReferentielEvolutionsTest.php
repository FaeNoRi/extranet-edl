<?php

namespace Tests\Feature\Admin;

use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use App\Support\NiveauxReferentiel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Retours de réunion d'octobre 2026 : niveaux EDL, langue, badge, documents communs par code.
 */
class ReferentielEvolutionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
    }

    private function donnees(array $extra = []): array
    {
        return array_merge([
            'module' => 'Grammaire',
            'code' => 'G-C1',
            'contenu' => 'Les pronoms',
            'niveaux' => ['essentiel'],
        ], $extra);
    }

    public function test_les_anciens_niveaux_cecrl_sont_convertis(): void
    {
        $this->assertSame(['essentiel'], NiveauxReferentiel::depuisCecrl(['A1', 'A2']));
        $this->assertSame(['essentiel', 'consolidation'], NiveauxReferentiel::depuisCecrl(['B1', 'A2', 'B2']));
        $this->assertSame(['perfectionnement'], NiveauxReferentiel::depuisCecrl(['C1', 'C2']));
        $this->assertSame(['consolidation'], NiveauxReferentiel::depuisCecrl(['consolidation']));
        $this->assertSame([], NiveauxReferentiel::depuisCecrl([]));
        $this->assertSame('Essentiel, Perfectionnement', NiveauxReferentiel::afficher(['perfectionnement', 'essentiel']));
        $this->assertSame('tous niveaux', NiveauxReferentiel::afficher([]));
    }

    public function test_les_anciens_niveaux_ne_sont_plus_acceptes(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.referentiel.store'), $this->donnees(['niveaux' => ['A1']]))
            ->assertSessionHasErrors('niveaux.0');
    }

    public function test_langue_et_badge_sont_enregistres(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.referentiel.store'), $this->donnees(['langue' => 'Espagnol', 'badge' => 'Incontournable']))
            ->assertSessionHasNoErrors();

        $entree = Referentiel::firstOrFail();
        $this->assertSame('Espagnol', $entree->langue);
        $this->assertSame('Incontournable', $entree->badge);

        // Langue vide = toutes langues.
        $this->actingAs($this->admin)
            ->put(route('admin.referentiel.update', $entree), $this->donnees(['langue' => '', 'badge' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull($entree->fresh()->langue);
        $this->assertNull($entree->fresh()->badge);
    }

    public function test_un_meme_code_peut_exister_dans_plusieurs_langues_mais_pas_deux_fois_dans_la_meme(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.referentiel.store'), $this->donnees(['langue' => 'Anglais']))->assertSessionHasNoErrors();
        $this->post(route('admin.referentiel.store'), $this->donnees(['langue' => 'Espagnol']))->assertSessionHasNoErrors();
        $this->post(route('admin.referentiel.store'), $this->donnees())->assertSessionHasNoErrors(); // toutes langues

        $this->post(route('admin.referentiel.store'), $this->donnees(['langue' => 'Anglais']))->assertSessionHasErrors('code');
        $this->post(route('admin.referentiel.store'), $this->donnees())->assertSessionHasErrors('code');

        $this->assertSame(3, Referentiel::count());
    }

    public function test_la_liste_se_filtre_par_langue(): void
    {
        Referentiel::factory()->create(['code' => 'CODE-ANG', 'langue' => 'Anglais']);
        Referentiel::factory()->create(['code' => 'CODE-ESP', 'langue' => 'Espagnol']);
        Referentiel::factory()->create(['code' => 'CODE-COMMUN', 'langue' => null]);

        $this->actingAs($this->admin)->get(route('admin.referentiel.index', ['langue' => 'Anglais']))
            ->assertSee('CODE-ANG')->assertDontSee('CODE-ESP')->assertDontSee('CODE-COMMUN');

        $this->actingAs($this->admin)->get(route('admin.referentiel.index', ['langue' => 'toutes']))
            ->assertSee('CODE-COMMUN')->assertDontSee('CODE-ANG');
    }

    public function test_le_formulaire_de_seance_ne_propose_que_la_langue_de_la_session_et_les_communes(): void
    {
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id, 'langue' => 'Anglais']);
        Referentiel::factory()->create(['code' => 'CODE-ANG', 'langue' => 'Anglais', 'badge' => 'Badge visible']);
        Referentiel::factory()->create(['code' => 'CODE-ESP', 'langue' => 'Espagnol']);
        Referentiel::factory()->create(['code' => 'CODE-COMMUN', 'langue' => null]);

        $this->actingAs($formateur)->get(route('formateur.seances.create', $session))
            ->assertOk()
            ->assertSee('CODE-ANG')->assertSee('CODE-COMMUN')->assertDontSee('CODE-ESP')
            ->assertSee('Badge visible');
    }

    public function test_ajout_et_retrait_de_documents_communs(): void
    {
        $entree = Referentiel::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.referentiel.documents.store', $entree), [
            'documents' => [UploadedFile::fake()->create('fiche-pronoms.pdf', 20, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $document = $entree->ressources()->firstOrFail();
        $this->assertSame('fiche-pronoms', $document->nom);
        $this->assertNull($document->session_formation_id);
        Storage::assertExists($document->chemin_fichier);

        $this->actingAs($this->admin)->get(route('admin.referentiel.edit', $entree))->assertSee('fiche-pronoms');
        $this->actingAs($this->admin)->get(route('admin.ressources.download', $document))->assertOk();

        $this->actingAs($this->admin)
            ->delete(route('admin.referentiel.documents.destroy', [$entree, $document]))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $entree->ressources()->count());
        $this->assertModelMissing($document);
        Storage::assertMissing($document->chemin_fichier);
    }

    public function test_un_fichier_non_autorise_est_refuse(): void
    {
        $entree = Referentiel::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.referentiel.documents.store', $entree), [
            'documents' => [UploadedFile::fake()->create('script.php', 1)],
        ])->assertSessionHasErrors('documents.0');

        $this->assertSame(0, Ressource::count());
    }

    public function test_seul_l_administrateur_gere_les_documents_communs(): void
    {
        $entree = Referentiel::factory()->create();

        $this->actingAs(User::factory()->formateur()->create())->post(route('admin.referentiel.documents.store', $entree), [
            'documents' => [UploadedFile::fake()->create('fiche.pdf', 5, 'application/pdf')],
        ])->assertForbidden();
    }

    public function test_les_documents_communs_suivent_le_code_dans_chaque_seance(): void
    {
        $entree = Referentiel::factory()->create(['badge' => 'Badge interne']);
        Storage::put('referentiel/fiche.pdf', "%PDF-1.4\n%%EOF");
        $document = Ressource::factory()->create(['nom' => 'Fiche commune', 'chemin_fichier' => 'referentiel/fiche.pdf', 'nom_fichier_original' => 'fiche.pdf', 'session_formation_id' => null]);
        $entree->ressources()->attach($document->id);

        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->fpc()->create(['formateur_id' => $formateur->id]);
        $stagiaire = User::factory()->stagiaireFpc()->create();
        $session->stagiaires()->attach($stagiaire->id);
        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'formateur_id' => $formateur->id, 'user_id' => $stagiaire->id, 'date' => Carbon::yesterday()]);
        $seance->referentiels()->attach($entree->id);

        // Formateur : visible et téléchargeable, badge affiché.
        $this->actingAs($formateur)->get(route('formateur.seances.show', $seance))->assertSee('Fiche commune')->assertSee('Badge interne');
        $this->actingAs($formateur)->get(route('formateur.ressources.download', $document))->assertOk();

        // Stagiaire : visible et consultable, sans le badge (réservé admin/formateurs).
        $this->actingAs($stagiaire)->get(route('stagiaire.ressources.show', $seance))->assertSee('Fiche commune')->assertDontSee('Badge interne');
        $this->actingAs($stagiaire)->get(route('stagiaire.ressources.download', $document))->assertOk();

        // Un stagiaire dont aucune séance ne coche ce code n'y a pas accès.
        $autre = User::factory()->stagiaireFpc()->create();
        SessionFormation::factory()->fpc()->create()->stagiaires()->attach($autre->id);
        $this->actingAs($autre)->get(route('stagiaire.ressources.download', $document))->assertForbidden();
    }

    public function test_supprimer_une_entree_retire_ses_documents(): void
    {
        $entree = Referentiel::factory()->create();
        Storage::put('referentiel/a.pdf', 'x');
        $document = Ressource::factory()->create(['chemin_fichier' => 'referentiel/a.pdf', 'session_formation_id' => null]);
        $entree->ressources()->attach($document->id);

        $this->actingAs($this->admin)->delete(route('admin.referentiel.destroy', $entree))->assertSessionHasNoErrors();

        $this->assertModelMissing($entree);
        $this->assertModelMissing($document);
        Storage::assertMissing('referentiel/a.pdf');
    }
}
