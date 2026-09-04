<?php

namespace Tests\Feature\Admin;

use App\Models\Document;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_ajout_d_un_document_de_structure(): void
    {
        $this->post(route('admin.documents.store'), [
            'nom' => 'Registre d\'accessibilité',
            'categorie' => 'presentation_structure',
            'fichier' => UploadedFile::fake()->create('registre.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $document = Document::firstOrFail();
        $this->assertNull($document->session_formation_id);
        $this->assertSame('presentation_structure', $document->categorie);
        Storage::assertExists($document->chemin_fichier);
    }

    public function test_ajout_d_un_document_de_session(): void
    {
        $session = SessionFormation::factory()->fpc()->create();

        $this->post(route('admin.documents.store'), [
            'nom' => 'Convention ou contrat',
            'categorie' => 'mes_documents',
            'session_formation_id' => $session->id,
            'fichier' => UploadedFile::fake()->create('convention.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'session_formation_id' => $session->id,
            'categorie' => 'mes_documents',
        ]);
    }

    public function test_suppression(): void
    {
        $this->post(route('admin.documents.store'), [
            'nom' => 'Catalogue de formations',
            'categorie' => 'presentation_structure',
            'fichier' => UploadedFile::fake()->create('cat.pdf', 10, 'application/pdf'),
        ]);
        $document = Document::firstOrFail();

        $this->delete(route('admin.documents.destroy', $document))->assertRedirect();
        $this->assertModelMissing($document);
        Storage::assertMissing($document->chemin_fichier);
    }
}
