<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Questionnaire;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stagiaire = auth()->user();
        $session = $stagiaire->sessionStagiaire()?->load('formateur', 'formateurs');

        $documentsStructure = Document::structure()
            ->categorie('presentation_structure')
            ->orderBy('nom')
            ->get();

        $mesDocuments = $session
            ? Document::where('session_formation_id', $session->id)->orderBy('nom')->get()
            : collect();

        $planning = $session
            ? $session->jours()->actifs()->orderBy('date')->get()
            : collect();

        // Tuiles du haut de page : repères calculés sur le planning déjà chargé.
        $prochaineSeance = $planning->first(fn ($jour) => $jour->date->isToday() || $jour->date->isFuture());
        $nbSeancesRealisees = $planning->filter(fn ($jour) => $jour->date->lte(today()))->count();

        $nbQuestionnairesAFaire = $session
            ? Questionnaire::pourSession($session->id)
                ->whereDoesntHave('repondants', fn ($q) => $q->whereKey($stagiaire->id))
                ->count()
            : 0;

        return view('stagiaire.dashboard', compact(
            'stagiaire', 'session', 'documentsStructure', 'mesDocuments', 'planning',
            'prochaineSeance', 'nbSeancesRealisees', 'nbQuestionnairesAFaire',
        ));
    }
}
