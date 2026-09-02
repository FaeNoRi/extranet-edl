<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GescofImport;
use App\Services\Gescof\GescofImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GescofImportController extends Controller
{
    public function index(): View
    {
        return view('admin.imports.index', [
            'imports' => GescofImport::with('auteur')->latest()->paginate(15),
        ]);
    }

    public function show(GescofImport $import): View
    {
        $fichierPresent = $import->fichier_path && Storage::exists($import->fichier_path);

        return view('admin.imports.show', compact('import', 'fichierPresent'));
    }

    /**
     * Téléverse un fichier et lance une simulation (aucune écriture).
     */
    public function simuler(Request $request, GescofImporter $importer): RedirectResponse
    {
        $request->validate([
            'fichier' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120'],
        ], [], ['fichier' => 'fichier GESCOF']);

        $fichier = $request->file('fichier');
        $chemin = $fichier->store('gescof');
        $absolu = Storage::path($chemin);

        try {
            $rapport = $importer->simuler($absolu, $request->user(), $chemin, $fichier->getClientOriginalName());
        } catch (\Throwable $e) {
            Storage::delete($chemin);

            return back()->with('erreur', "Fichier illisible : {$e->getMessage()}");
        }

        return redirect()->route('admin.imports.show', $rapport->import)
            ->with('succes', 'Simulation effectuée. Vérifiez le rapport avant d\'appliquer.');
    }

    /**
     * Applique un import précédemment simulé (réutilise le fichier téléversé).
     */
    public function appliquer(Request $request, GescofImport $import, GescofImporter $importer): RedirectResponse
    {
        abort_if($import->applique, 404);

        if (! $import->fichier_path || ! Storage::exists($import->fichier_path)) {
            return back()->with('erreur', 'Le fichier de cette simulation n\'est plus disponible. Relancez une simulation.');
        }

        $envoyer = $request->boolean('envoyer_acces');
        $rapport = $importer->appliquer(
            Storage::path($import->fichier_path),
            $request->user(),
            $envoyer,
            $import->fichier_path,
            $import->fichier_nom,
        );

        Storage::delete($import->fichier_path);
        $import->update(['fichier_path' => null]);

        return redirect()->route('admin.imports.show', $rapport->import)
            ->with('succes', "Import appliqué : {$rapport->comptesCrees} compte(s) créé(s), {$rapport->sessionsCreees} session(s) créée(s).");
    }
}
