<?php

namespace Tests\Feature\Stagiaire;

use App\Models\Document;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Dissuasion standard contre la capture pour les stagiaires OP : pas de
 * téléchargement (même en forçant l'URL), consultation via lecteur durci
 * uniquement. Les stagiaires FPC ne sont pas concernés par cette restriction.
 */
class LecteurDurciTest extends TestCase
{
    use RefreshDatabase;

    private function inscrire(User $stagiaire, SessionFormation $session): void
    {
        $session->stagiaires()->attach($stagiaire->id);
    }

    public function test_un_stagiaire_op_ne_peut_pas_telecharger_un_document(): void
    {
        Storage::fake('local');
        $document = Document::factory()->structure()->create();
        Storage::put($document->chemin_fichier, 'contenu');
        $stagiaire = User::factory()->stagiaireOp()->create();

        $response = $this->actingAs($stagiaire)->get(route('stagiaire.documents.download', $document));

        $response->assertOk();
        $this->assertStringStartsWith('inline', $response->headers->get('content-disposition'));
    }

    public function test_un_stagiaire_fpc_peut_telecharger_un_document(): void
    {
        Storage::fake('local');
        $document = Document::factory()->structure()->create();
        Storage::put($document->chemin_fichier, 'contenu');
        $stagiaire = User::factory()->stagiaireFpc()->create();

        $response = $this->actingAs($stagiaire)->get(route('stagiaire.documents.download', $document));

        $response->assertOk();
        $this->assertStringStartsWith('attachment', $response->headers->get('content-disposition'));
    }

    public function test_un_stagiaire_op_ne_peut_pas_telecharger_une_ressource_meme_sans_apercu(): void
    {
        Storage::fake('local');
        $session = SessionFormation::factory()->op()->create();
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, $session);

        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => now()->subDay()]);
        $ressource = Ressource::factory()->create(['nb_telechargement' => 0]);
        Storage::put($ressource->chemin_fichier, 'contenu');
        $seance->ressources()->attach($ressource->id, ['transmis' => true]);

        // Ni le paramètre apercu, ni son absence, ne permettent un vrai téléchargement pour l'OP.
        $response = $this->actingAs($stagiaire)->get(route('stagiaire.ressources.download', $ressource));

        $response->assertOk();
        $this->assertStringStartsWith('inline', $response->headers->get('content-disposition'));
        $this->assertSame(0, $ressource->fresh()->nb_telechargement);
    }

    public function test_un_stagiaire_fpc_peut_telecharger_une_ressource(): void
    {
        Storage::fake('local');
        $session = SessionFormation::factory()->fpc()->create();
        $stagiaire = User::factory()->stagiaireFpc()->create();
        $this->inscrire($stagiaire, $session);

        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => now()->subDay()]);
        $ressource = Ressource::factory()->create(['nb_telechargement' => 0]);
        Storage::put($ressource->chemin_fichier, 'contenu');
        $seance->ressources()->attach($ressource->id, ['transmis' => true]);

        $response = $this->actingAs($stagiaire)->get(route('stagiaire.ressources.download', $ressource));

        $response->assertOk();
        $this->assertStringStartsWith('attachment', $response->headers->get('content-disposition'));
        $this->assertSame(1, $ressource->fresh()->nb_telechargement);
    }

    public function test_apercu_document_accessible_a_un_stagiaire_op(): void
    {
        Storage::fake('local');
        $document = Document::factory()->structure()->create(['nom' => 'Livret d\'accueil']);
        Storage::put($document->chemin_fichier, 'contenu');
        $stagiaire = User::factory()->stagiaireOp()->create();

        $this->actingAs($stagiaire)->get(route('stagiaire.documents.apercu', $document))
            ->assertOk()
            ->assertSee('Livret d\'accueil');
    }

    public function test_le_filigrane_est_toujours_le_nom_de_la_structure(): void
    {
        Storage::fake('local');
        $document = Document::factory()->structure()->create();
        Storage::put($document->chemin_fichier, 'contenu');
        $stagiaire = User::factory()->stagiaireOp()->create(['nom' => 'ROYER', 'prenom' => 'Daniel', 'login' => 'droyer-op']);

        $this->actingAs($stagiaire)->get(route('stagiaire.documents.apercu', $document))
            ->assertOk()
            ->assertSee(config('edl.structure.nom'))
            ->assertDontSee('droyer-op');
    }

    public function test_un_stagiaire_op_ne_peut_pas_ouvrir_le_fichier_directement_dans_le_navigateur(): void
    {
        Storage::fake('local');
        $document = Document::factory()->structure()->create();
        Storage::put($document->chemin_fichier, 'contenu');
        $stagiaire = User::factory()->stagiaireOp()->create();
        $url = route('stagiaire.documents.download', $document);

        // Navigation directe ou iframe : le lecteur natif (avec enregistrer/imprimer) serait utilisé.
        foreach (['document', 'iframe', 'embed', 'object'] as $destination) {
            $this->actingAs($stagiaire)->withHeaders(['Sec-Fetch-Dest' => $destination])->get($url)->assertForbidden();
        }

        // Appels du lecteur durci (fetch PDF.js, <video>, <img>) : autorisés, jamais mis en cache.
        foreach (['empty', 'video', 'image'] as $destination) {
            $this->actingAs($stagiaire)->withHeaders(['Sec-Fetch-Dest' => $destination])->get($url)
                ->assertOk()
                ->assertHeader('Cache-Control', 'no-store, private');
        }
    }

    public function test_le_lecteur_pdf_n_embarque_pas_le_lecteur_natif_du_navigateur(): void
    {
        Storage::fake('local');
        $document = Document::factory()->structure()->create(['nom_fichier_original' => 'plan.pdf']);
        Storage::put($document->chemin_fichier, 'contenu');
        $stagiaire = User::factory()->stagiaireOp()->create();

        $this->actingAs($stagiaire)->get(route('stagiaire.documents.apercu', $document))
            ->assertOk()
            ->assertSee('data-lecteur-pdf', false)
            ->assertDontSee('<iframe', false);
    }

    public function test_un_stagiaire_op_consulte_une_ressource_dans_le_lecteur_plein_page(): void
    {
        Storage::fake('local');
        $session = SessionFormation::factory()->op()->create();
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, $session);
        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => now()->subDay()]);
        $ressource = Ressource::factory()->create(['nom' => 'Fiche exercices', 'type_fichier' => 'pdf']);
        Storage::put($ressource->chemin_fichier, 'contenu');
        $seance->ressources()->attach($ressource->id, ['transmis' => true]);

        $this->actingAs($stagiaire)->get(route('stagiaire.ressources.show', $seance))
            ->assertOk()
            ->assertSee(route('stagiaire.ressources.apercu', $ressource), false)
            ->assertDontSee('<iframe', false);

        $this->actingAs($stagiaire)->get(route('stagiaire.ressources.apercu', $ressource))
            ->assertOk()
            ->assertSee('data-lecteur-pdf', false);
    }

    public function test_apercu_document_refuse_pour_une_autre_session(): void
    {
        Storage::fake('local');
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, SessionFormation::factory()->op()->create());

        $autreDoc = Document::factory()->mesDocuments()->create([
            'session_formation_id' => SessionFormation::factory()->op()->create()->id,
        ]);

        $this->actingAs($stagiaire)->get(route('stagiaire.documents.apercu', $autreDoc))->assertForbidden();
    }

    public function test_le_lien_de_document_pointe_vers_l_apercu_pour_un_stagiaire_op(): void
    {
        $document = Document::factory()->structure()->create(['nom' => 'Livret d\'accueil']);
        $stagiaire = User::factory()->stagiaireOp()->create();

        $this->actingAs($stagiaire)->get(route('stagiaire.dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('stagiaire.documents.apercu', $document).'"', false)
            ->assertDontSee('href="'.route('stagiaire.documents.download', $document).'"', false);
    }

    public function test_le_lien_de_ressource_ne_propose_pas_de_telechargement_pour_un_stagiaire_op(): void
    {
        $session = SessionFormation::factory()->op()->create();
        $stagiaire = User::factory()->stagiaireOp()->create();
        $this->inscrire($stagiaire, $session);

        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'date' => now()->subDay()]);
        $ressource = Ressource::factory()->create(['nom' => 'Fiche exercices']);
        $seance->ressources()->attach($ressource->id, ['transmis' => true]);

        $this->actingAs($stagiaire)->get(route('stagiaire.ressources.show', $seance))
            ->assertOk()
            ->assertDontSee('↓');
    }
}
