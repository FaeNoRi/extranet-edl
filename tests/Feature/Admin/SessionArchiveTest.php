<?php

namespace Tests\Feature\Admin;

use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class SessionArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_archive_zip_d_une_session_fpc(): void
    {
        $session = SessionFormation::factory()->fpc()->create();
        $seance = Seance::factory()->create([
            'session_formation_id' => $session->id,
            'date' => '2026-03-10',
        ]);

        Storage::put('ressources/present.mp3', 'audio');
        $presente = Ressource::factory()->create(['chemin_fichier' => 'ressources/present.mp3', 'nom_fichier_original' => 'chanson.mp3']);
        $absente = Ressource::factory()->create(['chemin_fichier' => 'ressources/absent.mp4', 'nom_fichier_original' => 'video.mp4']);
        $seance->ressources()->attach([$presente->id, $absente->id]);

        $response = $this->get(route('admin.sessions.archive', $session));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');

        $chemin = tempnam(sys_get_temp_dir(), 'test').'.zip';
        file_put_contents($chemin, $response->streamedContent());

        $zip = new ZipArchive;
        $zip->open($chemin);
        $fichiers = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fichiers[] = $zip->getNameIndex($i);
        }

        $this->assertContains('2026-03-10/10-03-2026.RP1.mp3', $fichiers);
        $manifeste = $zip->getFromName('MANIFESTE.txt');
        $this->assertStringContainsString('ABSENT', $manifeste);
        $this->assertStringContainsString('fiche pedagogique', $manifeste);

        $zip->close();
        @unlink($chemin);
    }

    public function test_pas_d_archive_pour_une_session_op(): void
    {
        $session = SessionFormation::factory()->op()->create();

        $this->get(route('admin.sessions.archive', $session))->assertNotFound();
    }
}
