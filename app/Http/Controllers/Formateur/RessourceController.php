<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Ressource;
use App\Models\SessionFormation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RessourceController extends Controller
{
    public function store(Request $request, SessionFormation $session): RedirectResponse
    {
        $this->autoriser($session);

        $data = $request->validate([
            'fichiers' => ['required', 'array'],
            'fichiers.*' => ['file', 'max:51200'],
        ], [], ['fichiers.*' => 'fichier']);

        foreach ($data['fichiers'] as $fichier) {
            Ressource::create([
                'nom' => pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME),
                'type_fichier' => $this->type($fichier),
                'chemin_fichier' => $fichier->store("sessions/{$session->id}/ressources"),
                'nom_fichier_original' => $fichier->getClientOriginalName(),
                'taille' => $fichier->getSize(),
                'uploader_id' => $request->user()->id,
                'session_formation_id' => $session->id,
            ]);
        }

        return back()->with('succes', count($data['fichiers']).' ressource(s) déposée(s).');
    }

    public function download(Ressource $ressource): StreamedResponse
    {
        abort_unless(
            $ressource->session_formation_id
                && $this->encadre(SessionFormation::find($ressource->session_formation_id)),
            403,
        );
        abort_unless(Storage::exists($ressource->chemin_fichier), 404);

        $ressource->increment('nb_telechargement');

        return Storage::download($ressource->chemin_fichier, $ressource->nom_fichier_original);
    }

    public function destroy(Ressource $ressource): RedirectResponse
    {
        $session = SessionFormation::find($ressource->session_formation_id);
        abort_unless($session && $this->encadre($session), 403);

        Storage::delete($ressource->chemin_fichier);
        $ressource->delete();

        return back()->with('succes', 'Ressource supprimée.');
    }

    private function type($fichier): string
    {
        return match (true) {
            str_starts_with((string) $fichier->getMimeType(), 'audio/') => 'audio',
            str_starts_with((string) $fichier->getMimeType(), 'video/') => 'video',
            str_starts_with((string) $fichier->getMimeType(), 'image/') => 'image',
            $fichier->getClientOriginalExtension() === 'pdf' => 'pdf',
            default => 'autre',
        };
    }

    private function encadre(?SessionFormation $session): bool
    {
        return $session && ($session->formateur_id === auth()->id()
            || $session->formateurs()->whereKey(auth()->id())->exists());
    }

    private function autoriser(SessionFormation $session): void
    {
        abort_unless($this->encadre($session), 403);
    }
}
