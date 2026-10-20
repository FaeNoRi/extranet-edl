<?php

namespace Tests\Feature\Admin;

use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * Téléchargement de toutes les fiches pédagogiques d'une session (retour de réunion).
 */
class FichesSessionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
    }

    /** @return array<int, string> noms des fichiers de l'archive téléchargée */
    private function contenuZip(SessionFormation $session): array
    {
        $reponse = $this->actingAs($this->admin)->get(route('admin.sessions.fiches', $session));
        $reponse->assertOk();
        $this->assertStringContainsString('fiches-pedagogiques-', $reponse->headers->get('content-disposition'));

        $fichier = tempnam(sys_get_temp_dir(), 'test').'.zip';
        file_put_contents($fichier, $reponse->streamedContent());
        $zip = new ZipArchive;
        $zip->open($fichier);
        $noms = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $noms[] = $zip->getNameIndex($i);
            $this->assertStringStartsWith('%PDF', $zip->getFromIndex($i));
        }
        $zip->close();
        @unlink($fichier);

        return $noms;
    }

    public function test_toutes_les_fiches_d_une_session_op_dans_l_ordre_chronologique(): void
    {
        $session = SessionFormation::factory()->op()->create();
        Seance::factory()->create(['session_formation_id' => $session->id, 'date' => '2026-10-14']);
        Seance::factory()->create(['session_formation_id' => $session->id, 'date' => '2026-10-07']);
        Seance::factory()->create(['session_formation_id' => $session->id, 'date' => '2026-10-07']); // même jour

        $this->assertSame([
            '2026-10-07 - fiche pedagogique.pdf',
            '2026-10-07 - fiche pedagogique (2).pdf',
            '2026-10-14 - fiche pedagogique.pdf',
        ], $this->contenuZip($session));
    }

    public function test_session_fpc_les_fichiers_portent_le_nom_du_stagiaire_et_les_pdf_manquants_sont_generes(): void
    {
        $session = SessionFormation::factory()->fpc()->create();
        $stagiaire = User::factory()->stagiaireFpc()->create(['nom' => 'MARTIN', 'prenom' => 'Lucie']);
        $seance = Seance::factory()->create(['session_formation_id' => $session->id, 'user_id' => $stagiaire->id, 'date' => '2026-10-07', 'fiche_pdf_path' => null]);

        $noms = $this->contenuZip($session);

        $this->assertCount(1, $noms);
        $this->assertStringStartsWith('2026-10-07 - ', $noms[0]);
        $this->assertStringContainsString('martin', strtolower($noms[0]));
        $this->assertNotNull($seance->fresh()->fiche_pdf_path, 'La fiche manquante a été générée et enregistrée.');
    }

    public function test_une_session_sans_seance_renvoie_un_message(): void
    {
        $session = SessionFormation::factory()->op()->create();

        $this->actingAs($this->admin)->from(route('admin.sessions.show', $session))
            ->get(route('admin.sessions.fiches', $session))
            ->assertRedirect(route('admin.sessions.show', $session))
            ->assertSessionHas('erreur');
    }

    public function test_le_bouton_est_present_pour_op_et_fpc_et_reserve_a_l_admin(): void
    {
        $op = SessionFormation::factory()->op()->create();
        $fpc = SessionFormation::factory()->fpc()->create();

        $this->actingAs($this->admin)->get(route('admin.sessions.show', $op))
            ->assertSee('Fiches pédagogiques (ZIP)')->assertDontSee('Dossier complet (ZIP)');
        $this->actingAs($this->admin)->get(route('admin.sessions.show', $fpc))
            ->assertSee('Fiches pédagogiques (ZIP)')->assertSee('Dossier complet (ZIP)');

        $this->actingAs(User::factory()->formateur()->create())
            ->get(route('admin.sessions.fiches', $op))->assertForbidden();
    }
}
