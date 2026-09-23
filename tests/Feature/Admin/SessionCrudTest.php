<?php

namespace Tests\Feature\Admin;

use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_creation_session_op_exige_un_rythme(): void
    {
        $this->post(route('admin.sessions.store'), [
            'num_GESCOF' => '260999A', 'nom' => 'Anglais test',
            'code_produit' => 'OP', 'langue' => 'Anglais',
        ])->assertSessionHasErrors('rythme_op');
    }

    public function test_langue_doit_faire_partie_de_la_liste_autorisee(): void
    {
        $this->post(route('admin.sessions.store'), [
            'num_GESCOF' => '260999A', 'nom' => 'Test', 'code_produit' => 'FPC', 'langue' => 'Klingon',
        ])->assertSessionHasErrors('langue');

        $this->assertDatabaseMissing('session_formations', ['num_GESCOF' => '260999A']);
    }

    public function test_creation_session_avec_nouveau_client_et_equipe(): void
    {
        $ref = User::factory()->formateur()->create();
        $co = User::factory()->formateur()->create();

        $this->post(route('admin.sessions.store'), [
            'num_GESCOF' => '260999A',
            'nom' => 'Anglais professionnel',
            'code_produit' => 'FPC',
            'langue' => 'Anglais',
            'nouveau_client' => 'ACME',
            'formateur_id' => $ref->id,
            'formateurs' => [$co->id],
            'distanciel' => '1',
        ])->assertRedirect();

        $session = SessionFormation::where('num_GESCOF', '260999A')->firstOrFail();
        $this->assertSame('ACME', $session->client->nom);
        $this->assertTrue($session->distanciel);
        // Le référent est toujours dans l'équipe, marqué principal.
        $this->assertEqualsCanonicalizing([$ref->id, $co->id], $session->formateurs->pluck('id')->all());
        $this->assertTrue((bool) $session->formateurs->firstWhere('id', $ref->id)->pivot->principal);
    }

    public function test_numero_gescof_unique(): void
    {
        SessionFormation::factory()->create(['num_GESCOF' => '260070A']);

        $this->post(route('admin.sessions.store'), [
            'num_GESCOF' => '260070A', 'nom' => 'x', 'code_produit' => 'FPC', 'langue' => 'Anglais',
        ])->assertSessionHasErrors('num_GESCOF');
    }

    public function test_synchronisation_du_planning(): void
    {
        $session = SessionFormation::factory()->op()->create();

        $this->post(route('admin.sessions.planning.sync', $session), [
            'nouvelles_dates' => '10/09/2026 17/09/2026, 24/09/2026',
        ])->assertRedirect();

        $this->assertSame(3, $session->jours()->count());

        $jours = $session->jours()->orderBy('date')->get();
        $premier = $jours->first();
        // Décoche le premier jour, garde les deux autres. Les ids sont envoyés en
        // chaînes de caractères, comme le fait un vrai formulaire HTML (cases à
        // cocher) — un tableau d'ids déjà typés int masquerait un bug de conversion.
        $autres = $jours->skip(1)->pluck('id')->map(fn ($id) => (string) $id)->all();

        $this->post(route('admin.sessions.planning.sync', $session), ['actifs' => $autres]);

        $this->assertFalse($premier->fresh()->actif);
        $this->assertSame(2, $session->jours()->actifs()->count());
        foreach ($jours->skip(1) as $jour) {
            $this->assertTrue($jour->fresh()->actif, "Le jour #{$jour->id} coché doit être actif.");
        }
    }

    public function test_reproduction_cocher_des_jours_inactifs(): void
    {
        // Reproduit le cas signalé : une session avec plusieurs jours déjà en base,
        // un seul actif, sur laquelle on coche des jours supplémentaires.
        $session = SessionFormation::factory()->op()->create();
        $this->post(route('admin.sessions.planning.sync', $session), [
            'nouvelles_dates' => implode(' ', [
                '01/09/2026', '08/09/2026', '15/09/2026', '22/09/2026', '29/09/2026',
                '06/10/2026', '13/10/2026', '20/10/2026', '27/10/2026', '03/11/2026', '10/11/2026',
            ]),
        ])->assertRedirect();

        $jours = $session->jours()->orderBy('date')->get();
        // Ne garde que le premier actif, comme sur la copie d'écran.
        $this->post(route('admin.sessions.planning.sync', $session), ['actifs' => [(string) $jours->first()->id]]);
        $this->assertSame(1, $session->jours()->actifs()->count());

        // On coche 3 jours de plus en plus du premier, en chaînes de caractères
        // (comme le fait un vrai navigateur avec des cases à cocher).
        $nouveauxActifs = $jours->take(4)->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->post(route('admin.sessions.planning.sync', $session), ['actifs' => $nouveauxActifs])
            ->assertRedirect();

        $this->assertSame(4, $session->jours()->actifs()->count(), 'Les jours nouvellement cochés doivent être actifs.');
        foreach ($jours->take(4) as $jour) {
            $this->assertTrue($jour->fresh()->actif, "Le jour #{$jour->id} coché doit être actif.");
        }
    }

    public function test_suppression_session(): void
    {
        $session = SessionFormation::factory()->create();

        $this->delete(route('admin.sessions.destroy', $session))
            ->assertRedirect(route('admin.sessions.index'));

        $this->assertModelMissing($session);
    }
}
