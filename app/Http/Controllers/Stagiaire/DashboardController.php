<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Document;
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

        return view('stagiaire.dashboard', compact(
            'stagiaire', 'session', 'documentsStructure', 'mesDocuments', 'planning',
        ));
    }
}
