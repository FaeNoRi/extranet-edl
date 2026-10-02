<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Ressource;
use App\Models\SessionFormation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RessourceController extends Controller
{
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

    private function encadre(?SessionFormation $session): bool
    {
        if (! $session) {
            return false;
        }

        // L'admin peut aussi télécharger/supprimer une ressource, notamment
        // depuis la vue Séance exposée dans l'administration.
        return auth()->user()->isAdmin()
            || $session->formateur_id === auth()->id()
            || $session->formateurs()->whereKey(auth()->id())->exists();
    }
}
