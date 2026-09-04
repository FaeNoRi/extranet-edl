<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Ressource;
use App\Models\Seance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TelechargementController extends Controller
{
    public function document(Document $document): StreamedResponse
    {
        $session = auth()->user()->sessionStagiaire();

        // Document commun à la structure, ou document de la session du stagiaire.
        abort_unless(
            is_null($document->session_formation_id)
            || ($session && $document->session_formation_id === $session->id),
            403,
        );
        abort_unless(Storage::exists($document->chemin_fichier), 404);

        return Storage::download($document->chemin_fichier, $document->nom_fichier_original);
    }

    public function ressource(Request $request, Ressource $ressource): StreamedResponse
    {
        $session = auth()->user()->sessionStagiaire();
        abort_unless($session, 403);

        // La ressource doit être transmise via une séance réalisée de la
        // session du stagiaire, ou rattachée à un module du référentiel d'une
        // telle séance.
        abort_unless($this->ressourceAutorisee($ressource, $session->id), 403);
        abort_unless(Storage::exists($ressource->chemin_fichier), 404);

        if ($request->boolean('apercu')) {
            return Storage::response($ressource->chemin_fichier);
        }

        $ressource->increment('nb_telechargement');

        return Storage::download($ressource->chemin_fichier, $ressource->nom_fichier_original);
    }

    private function ressourceAutorisee(Ressource $ressource, int $sessionId): bool
    {
        $stagiaireId = auth()->id();

        $seances = Seance::where('session_formation_id', $sessionId)
            ->whereDate('date', '<=', Carbon::today())
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $stagiaireId));

        $transmise = (clone $seances)
            ->whereHas('ressources', fn ($q) => $q->whereKey($ressource->id)->wherePivot('transmis', true))
            ->exists();

        $referentiel = (clone $seances)
            ->whereHas('referentiels.ressources', fn ($q) => $q->whereKey($ressource->id))
            ->exists();

        return $transmise || $referentiel;
    }
}
