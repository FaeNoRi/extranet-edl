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
        // Décoche le premier jour, garde les deux autres.
        $autres = $jours->skip(1)->pluck('id')->all();

        $this->post(route('admin.sessions.planning.sync', $session), ['actifs' => $autres]);

        $this->assertFalse($premier->fresh()->actif);
        $this->assertSame(2, $session->jours()->actifs()->count());
    }

    public function test_suppression_session(): void
    {
        $session = SessionFormation::factory()->create();

        $this->delete(route('admin.sessions.destroy', $session))
            ->assertRedirect(route('admin.sessions.index'));

        $this->assertModelMissing($session);
    }
}
