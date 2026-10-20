<?php

namespace Tests\Feature\Formateur;

use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MesSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_session_fpc_affiche_le_premier_stagiaire(): void
    {
        $formateur = User::factory()->formateur()->create();
        $fpc = SessionFormation::factory()->fpc()->create(['formateur_id' => $formateur->id]);
        $fpc->stagiaires()->attach([
            User::factory()->stagiaireFpc()->create(['nom' => 'ZOLA', 'prenom' => 'Émile'])->id,
            User::factory()->stagiaireFpc()->create(['nom' => 'BERNARD', 'prenom' => 'Lucie'])->id,
        ]);

        $this->actingAs($formateur)->get(route('formateur.sessions.index'))
            ->assertOk()
            ->assertSee('BERNARD')
            ->assertDontSee('ZOLA')
            ->assertSee('+ 1 autre(s)');
    }

    public function test_une_session_fpc_a_un_seul_stagiaire_n_affiche_pas_de_compteur(): void
    {
        $formateur = User::factory()->formateur()->create();
        $fpc = SessionFormation::factory()->fpc()->create(['formateur_id' => $formateur->id]);
        $fpc->stagiaires()->attach(User::factory()->stagiaireFpc()->create(['nom' => 'MARTIN'])->id);

        $this->actingAs($formateur)->get(route('formateur.sessions.index'))
            ->assertSee('MARTIN')
            ->assertDontSee('autre(s)');
    }

    public function test_une_session_op_garde_seulement_le_nombre_de_stagiaires(): void
    {
        $formateur = User::factory()->formateur()->create();
        $op = SessionFormation::factory()->op()->create(['formateur_id' => $formateur->id]);
        $op->stagiaires()->attach(User::factory()->stagiaireOp()->create(['nom' => 'DURAND'])->id);

        $this->actingAs($formateur)->get(route('formateur.sessions.index'))
            ->assertSee('1 stagiaire(s)')
            ->assertDontSee('DURAND');
    }
}
