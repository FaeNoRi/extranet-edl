<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Emargement;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\SessionJour;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ModeleDomaineTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_adresse_email_peut_porter_plusieurs_comptes(): void
    {
        User::factory()->count(3)->create(['email' => 'contact@client.fr']);

        $this->assertSame(3, User::where('email', 'contact@client.fr')->count());
    }

    public function test_session_relie_formateur_client_et_stagiaires(): void
    {
        $session = SessionFormation::factory()
            ->for(Client::factory())
            ->create(['formateur_id' => User::factory()->formateur()]);

        $stagiaires = User::factory()->count(4)->stagiaireFpc()->create();
        $session->stagiaires()->attach($stagiaires->pluck('id'));

        $this->assertTrue($session->formateur->isFormateur());
        $this->assertInstanceOf(Client::class, $session->client);
        $this->assertCount(4, $session->fresh()->stagiaires);
    }

    public function test_ressource_de_seance_marquee_transmise_ou_non(): void
    {
        $seance = Seance::factory()->create();
        $transmise = Ressource::factory()->create();
        $interne = Ressource::factory()->create();

        $seance->ressources()->attach($transmise->id, ['transmis' => true]);
        $seance->ressources()->attach($interne->id, ['transmis' => false]);

        $this->assertCount(2, $seance->ressources);
        $this->assertCount(1, $seance->ressourcesTransmises);
        $this->assertTrue($seance->ressourcesTransmises->first()->is($transmise));
    }

    public function test_scope_jours_actifs(): void
    {
        $session = SessionFormation::factory()->op()->create();
        SessionJour::factory()->count(3)->create(['session_formation_id' => $session->id, 'actif' => true]);
        SessionJour::factory()->count(2)->create(['session_formation_id' => $session->id, 'actif' => false]);

        $this->assertSame(3, $session->jours()->actifs()->count());
    }

    public function test_fiche_pedagogique_fpc_est_rattachee_a_un_stagiaire(): void
    {
        $stagiaire = User::factory()->stagiaireFpc()->create();
        $seance = Seance::factory()->pourStagiaire($stagiaire)->create();

        $this->assertTrue($seance->stagiaire->is($stagiaire));
        $this->assertTrue($stagiaire->fichesPedagogiques->contains($seance));
    }

    public function test_emargement_unique_par_seance_et_stagiaire(): void
    {
        $emargement = Emargement::factory()->create();

        $this->expectException(QueryException::class);
        Emargement::factory()->create([
            'seance_id' => $emargement->seance_id,
            'user_id' => $emargement->user_id,
        ]);
    }

    public function test_les_modifications_sont_journalisees(): void
    {
        $seance = Seance::factory()->create(['contenu' => 'Contenu initial']);

        $seance->update(['contenu' => 'Contenu révisé']);

        $activity = Activity::forSubject($seance)->where('event', 'updated')->latest('id')->first();
        $this->assertNotNull($activity);
        $this->assertSame('Contenu initial', $activity->attribute_changes['old']['contenu']);
        $this->assertSame('Contenu révisé', $activity->attribute_changes['attributes']['contenu']);
    }
}
