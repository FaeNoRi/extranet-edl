<?php

namespace Tests\Feature\Admin;

use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Database\Seeders\ReferentielSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Trame v3 du référentiel : chargement sur une base existante, archivage de l'ancienne trame,
 * badges multiples, ordre des modules.
 */
class ReferentielTrameV3Test extends TestCase
{
    use RefreshDatabase;

    private function ancienneEntree(array $attributs = []): Referentiel
    {
        return Referentiel::factory()->create(array_merge(['module' => 'Methodologie', 'code' => 'M-C1', 'langue' => null], $attributs));
    }

    public function test_le_chargement_archive_les_entrees_utilisees_et_supprime_les_autres(): void
    {
        $utilisee = $this->ancienneEntree(['code' => 'C-C1']);
        $seance = Seance::factory()->create();
        $seance->referentiels()->attach($utilisee->id);

        $avecDocument = $this->ancienneEntree(['code' => 'V-C1']);
        $avecDocument->ressources()->attach(Ressource::factory()->create(['session_formation_id' => null])->id);

        $inutilisee = $this->ancienneEntree(['code' => 'A-C9']);

        $this->artisan('edl:referentiel-charger')->assertSuccessful();

        $this->assertNotNull($utilisee->fresh()->archive_le);
        $this->assertNotNull($avecDocument->fresh()->archive_le);
        $this->assertModelMissing($inutilisee);
        $this->assertSame(108, Referentiel::actives()->count());
        // L'ancienne fiche garde son module coché.
        $this->assertTrue($seance->fresh()->referentiels->contains($utilisee));
    }

    public function test_la_simulation_ne_modifie_rien(): void
    {
        $ancienne = $this->ancienneEntree();

        $this->artisan('edl:referentiel-charger', ['--simuler' => true])->assertSuccessful();

        $this->assertModelExists($ancienne);
        $this->assertSame(1, Referentiel::count());
    }

    public function test_le_chargement_est_rejouable_et_reactive_une_entree_de_la_trame(): void
    {
        $this->artisan('edl:referentiel-charger')->assertSuccessful();
        Referentiel::where('code', 'Conj1')->update(['archive_le' => now(), 'contenu' => 'Modifié']);

        $this->artisan('edl:referentiel-charger')->assertSuccessful();

        $conj1 = Referentiel::where('code', 'Conj1')->firstOrFail();
        $this->assertNull($conj1->archive_le);
        $this->assertSame('BE et HAVE', $conj1->contenu);
        $this->assertSame(108, Referentiel::count());
    }

    public function test_les_entrees_archivees_ne_sont_plus_proposees_sauf_si_deja_cochees(): void
    {
        $formateur = User::factory()->formateur()->create();
        $session = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id, 'langue' => 'Anglais']);
        $active = Referentiel::factory()->create(['code' => 'CODE-ACTIF', 'module' => 'Bases']);
        $archivee = Referentiel::factory()->create(['code' => 'CODE-ARCHIVE', 'module' => 'Bases', 'archive_le' => now()]);

        $this->actingAs($formateur)->get(route('formateur.seances.create', $session))
            ->assertSee('CODE-ACTIF')->assertDontSee('CODE-ARCHIVE');

        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'formateur_id' => $formateur->id]);
        $seance->referentiels()->attach($archivee->id);

        $this->actingAs($formateur)->get(route('formateur.seances.edit', $seance))
            ->assertSee('CODE-ACTIF')->assertSee('CODE-ARCHIVE');
    }

    public function test_la_liste_admin_masque_les_archivees_et_filtre_par_badge(): void
    {
        $admin = User::factory()->admin()->create();
        Referentiel::factory()->create(['code' => 'CODE-RH', 'badges' => ['RH', 'ORAL']]);
        Referentiel::factory()->create(['code' => 'CODE-LOG', 'badges' => ['LOG']]);
        Referentiel::factory()->create(['code' => 'CODE-ARCHIVE', 'archive_le' => now()]);

        $this->actingAs($admin)->get(route('admin.referentiel.index'))
            ->assertSee('CODE-RH')->assertSee('CODE-LOG')->assertDontSee('CODE-ARCHIVE')
            ->assertSee('Afficher les archivées (1)');

        $this->actingAs($admin)->get(route('admin.referentiel.index', ['badge' => 'RH']))
            ->assertSee('CODE-RH')->assertDontSee('CODE-LOG');

        $this->actingAs($admin)->get(route('admin.referentiel.index', ['archives' => 1]))
            ->assertSee('CODE-ARCHIVE')->assertSee('Archivée');
    }

    public function test_archiver_et_reactiver_depuis_le_formulaire(): void
    {
        $admin = User::factory()->admin()->create();
        $entree = Referentiel::factory()->create(['module' => 'Bases', 'code' => 'B1', 'contenu' => 'Alphabet']);
        $donnees = ['module' => 'Bases', 'code' => 'B1', 'contenu' => 'Alphabet', 'niveaux' => ['essentiel']];

        $this->actingAs($admin)->put(route('admin.referentiel.update', $entree), $donnees + ['archivee' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($entree->fresh()->estArchivee());

        $this->actingAs($admin)->put(route('admin.referentiel.update', $entree), $donnees)->assertSessionHasNoErrors();
        $this->assertFalse($entree->fresh()->estArchivee());
    }

    public function test_une_entree_archivee_garde_son_ancien_module(): void
    {
        $admin = User::factory()->admin()->create();
        $ancienne = $this->ancienneEntree(['archive_le' => now()]);

        $this->actingAs($admin)->put(route('admin.referentiel.update', $ancienne), [
            'module' => 'Methodologie', 'code' => 'M-C1', 'contenu' => 'Corrigé', 'archivee' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Corrigé', $ancienne->fresh()->contenu);

        // Mais une nouvelle entrée ne peut pas prendre un module retiré.
        $this->actingAs($admin)->post(route('admin.referentiel.store'), [
            'module' => 'Methodologie', 'code' => 'X1', 'contenu' => 'Test',
        ])->assertSessionHasErrors('module');
    }

    public function test_un_badge_trop_long_est_refuse(): void
    {
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.referentiel.store'), [
            'module' => 'Bases', 'code' => 'B99', 'contenu' => 'Test', 'badges' => 'ORAL, '.str_repeat('X', 31),
        ])->assertSessionHasErrors('badges');
    }

    public function test_modules_et_codes_dans_l_ordre_de_la_trame(): void
    {
        $this->seed(ReferentielSeeder::class);

        $html = $this->actingAs(User::factory()->admin()->create())->get(route('admin.referentiel.index'))->getContent();

        $this->assertMatchesRegularExpression('/>\s*Bases\s*<.*>\s*Conjugaison\s*<.*>\s*Construction de phrases\s*<.*>\s*Préparation au test CLOE\s*</s', $html);
        // Tri naturel : Conj2 avant Conj10.
        $this->assertLessThan(strpos($html, '>Conj10<'), strpos($html, '>Conj2<'));
    }
}
