<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /** Documents communs à la structure (présentation des locaux, registre, ...). */
    public function index(): View
    {
        return view('admin.documents.index', [
            'documents' => Document::structure()->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'categorie' => ['required', 'in:presentation_structure,mes_documents'],
            'type_document' => ['nullable', 'string', 'max:255'],
            'session_formation_id' => ['nullable', 'exists:session_formations,id'],
            'fichier' => ['required', 'file', 'max:20480'],
        ], [], ['fichier' => 'fichier']);

        $sessionId = $data['session_formation_id'] ?? null;
        $fichier = $request->file('fichier');
        $dossier = $sessionId ? "sessions/{$sessionId}/documents" : 'documents/structure';

        Document::create([
            'nom' => $data['nom'],
            'categorie' => $data['categorie'],
            'type_document' => $data['type_document'] ?? $data['nom'],
            'session_formation_id' => $sessionId,
            'chemin_fichier' => $fichier->store($dossier),
            'nom_fichier_original' => $fichier->getClientOriginalName(),
            'taille' => $fichier->getSize(),
            'uploader_id' => $request->user()->id,
        ]);

        return back()->with('succes', 'Document ajouté.');
    }

    public function download(Document $document): StreamedResponse
    {
        abort_unless(Storage::exists($document->chemin_fichier), 404);

        return Storage::download($document->chemin_fichier, $document->nom_fichier_original);
    }

    public function destroy(Document $document): RedirectResponse
    {
        Storage::delete($document->chemin_fichier);
        $document->delete();

        return back()->with('succes', 'Document supprimé.');
    }

    /** Types de documents « MES DOCUMENTS » attendus pour une session. */
    public static function typesMesDocuments(): array
    {
        return [
            'Convention ou contrat', "Guide d'animation", "Livret d'accueil",
            "Questionnaire d'évaluation à chaud", "Questionnaire d'évaluation à froid",
        ];
    }

    /** Types de documents « PRÉSENTATION DE LA STRUCTURE ». */
    public static function typesStructure(): array
    {
        return [
            'Présentation des locaux', "Registre d'accessibilité",
            'Catalogue de formations', 'Liste du matériel mis à disposition',
        ];
    }
}
