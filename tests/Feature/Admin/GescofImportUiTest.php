<?php

namespace Tests\Feature\Admin;

use App\Models\GescofImport;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GescofImportUiTest extends TestCase
{
    use RefreshDatabase;

    private function fichier(): UploadedFile
    {
        return new UploadedFile(
            base_path('tests/Fixtures/gescof/inscriptions.csv'),
            'inscriptions.csv',
            'text/csv',
            null,
            true,
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_simulation_via_upload_n_ecrit_rien(): void
    {
        $response = $this->post(route('admin.imports.simuler'), ['fichier' => $this->fichier()]);

        $import = GescofImport::firstOrFail();
        $response->assertRedirect(route('admin.imports.show', $import));

        $this->assertFalse($import->applique);
        $this->assertSame('inscriptions.csv', $import->fichier_nom);
        $this->assertNotNull($import->fichier_path);
        $this->assertGreaterThan(0, $import->comptes_crees);
        $this->assertSame(0, SessionFormation::count());
    }

    public function test_type_de_fichier_refuse(): void
    {
        $this->post(route('admin.imports.simuler'), [
            'fichier' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('fichier');
    }

    public function test_application_depuis_une_simulation(): void
    {
        $this->post(route('admin.imports.simuler'), ['fichier' => $this->fichier()]);
        $simulation = GescofImport::firstOrFail();

        $response = $this->post(route('admin.imports.appliquer', $simulation));

        $applique = GescofImport::where('applique', true)->firstOrFail();
        $response->assertRedirect(route('admin.imports.show', $applique));

        $this->assertGreaterThan(0, SessionFormation::count());
        $this->assertGreaterThan(0, User::where('role', 'stagiaire_op')->count());

        // Le fichier temporaire est nettoyé.
        $simulation->refresh();
        $this->assertNull($simulation->fichier_path);
    }

    public function test_on_ne_peut_pas_appliquer_deux_fois(): void
    {
        $this->post(route('admin.imports.simuler'), ['fichier' => $this->fichier()]);
        $simulation = GescofImport::firstOrFail();
        $this->post(route('admin.imports.appliquer', $simulation));

        $applique = GescofImport::where('applique', true)->firstOrFail();
        $this->post(route('admin.imports.appliquer', $applique))->assertNotFound();
    }
}
