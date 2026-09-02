<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.edit'))->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'nom' => 'Durand',
            'prenom' => 'Camille',
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('Durand', $user->nom);
        $this->assertSame('Camille', $user->prenom);
    }

    public function test_nom_and_prenom_are_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), ['nom' => '', 'prenom' => ''])
            ->assertSessionHasErrors(['nom', 'prenom']);
    }

    public function test_account_deletion_route_does_not_exist(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->delete('/profil')->assertStatus(405);
    }
}
