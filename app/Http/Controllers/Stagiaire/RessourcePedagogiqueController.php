<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use App\Models\SessionFormation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Ressources pédagogiques du stagiaire : un dossier par séance réalisée
 * (nom = date), du plus récent au plus ancien. Chaque dossier réunit les
 * documents transmis par le formateur et les fiches du référentiel des
 * modules vus. La fiche pédagogique PDF n'y figure jamais.
 */
class RessourcePedagogiqueController extends Controller
{
    public function index(): View
    {
        $session = auth()->user()->sessionStagiaire();

        $dossiers = $session
            ? $this->seancesRealisees($session)->with('stagiaire')->orderByDesc('date')->get()
            : collect();

        return view('stagiaire.ressources.index', compact('session', 'dossiers'));
    }

    public function show(Seance $seance): View
    {
        $this->autoriser($seance);

        $seance->load(['ressources', 'referentiels.ressources', 'sessionFormation']);

        $ressourcesTransmises = $seance->ressources->where('pivot.transmis', true);
        $fichesReferentiel = $seance->referentiels;

        return view('stagiaire.ressources.show', compact('seance', 'ressourcesTransmises', 'fichesReferentiel'));
    }

    /** Séances réalisées visibles par le stagiaire connecté. */
    private function seancesRealisees(SessionFormation $session): Builder
    {
        $stagiaireId = auth()->id();

        return Seance::where('session_formation_id', $session->id)
            ->whereDate('date', '<=', Carbon::today())
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $stagiaireId));
    }

    private function autoriser(Seance $seance): void
    {
        $session = auth()->user()->sessionStagiaire();

        abort_unless(
            $session
            && $seance->session_formation_id === $session->id
            && $seance->date->lte(Carbon::today())
            && (is_null($seance->user_id) || $seance->user_id === auth()->id()),
            403,
        );
    }
}
