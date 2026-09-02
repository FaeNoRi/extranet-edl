<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\SessionFormation;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FormateurCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_creation_d_un_formateur_avec_lien_d_acces(): void
    {
        Notification::fake();

        $this->post(route('admin.formateurs.store'), [
            'prenom' => 'Léa', 'nom' => 'mercier',
            'login' => 'lea.mercier', 'email' => 'lea@edl.fr',
            'formateur_op' => '1',
            'envoyer_acces' => '1',
        ])->assertRedirect(route('admin.formateurs.index'));

        $formateur = User::where('login', 'lea.mercier')->firstOrFail();
        $this->assertSame(Role::Formateur, $formateur->role);
        $this->assertSame('MERCIER', $formateur->nom);
        $this->assertTrue($formateur->formateur_op);
        Notification::assertSentTo($formateur, PasswordSetupLink::class);
    }

    public function test_un_formateur_doit_intervenir_en_fpc_ou_op(): void
    {
        $this->post(route('admin.formateurs.store'), [
            'prenom' => 'X', 'nom' => 'Y', 'login' => 'x.y', 'email' => 'x@y.fr',
        ])->assertSessionHasErrors('formateur_op');

        $this->assertDatabasemissing('users', ['login' => 'x.y']);
    }

    public function test_identifiant_unique(): void
    {
        User::factory()->create(['login' => 'pris']);

        $this->post(route('admin.formateurs.store'), [
            'prenom' => 'X', 'nom' => 'Y', 'login' => 'pris', 'email' => 'x@y.fr', 'formateur_op' => '1',
        ])->assertSessionHasErrors('login');
    }

    public function test_archivage_impossible_si_rattache_a_une_session(): void
    {
        $formateur = User::factory()->formateur()->create();
        SessionFormation::factory()->create(['formateur_id' => $formateur->id]);

        $this->delete(route('admin.formateurs.destroy', $formateur))
            ->assertSessionHas('erreur');

        $this->assertNull($formateur->fresh()->deleted_at);
    }

    public function test_archivage(): void
    {
        $formateur = User::factory()->formateur()->create();

        $this->delete(route('admin.formateurs.destroy', $formateur))
            ->assertRedirect(route('admin.formateurs.index'));

        $this->assertSoftDeleted($formateur);
    }
}
