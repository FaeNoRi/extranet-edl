<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\SessionFormation;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function index(): View
    {
        return view('formateur.sessions.index', [
            'sessions' => auth()->user()->sessionsPourFormateur()
                ->loadCount(['seances', 'stagiaires']),
        ]);
    }

    public function show(SessionFormation $session): View
    {
        $this->autoriser($session);

        $session->load([
            'client',
            'stagiaires' => fn ($q) => $q->orderBy('nom'),
            'seances' => fn ($q) => $q->with('stagiaire')->orderByDesc('date'),
            'jours' => fn ($q) => $q->orderBy('date'),
        ]);

        // Suivi de progression FPC : séances regroupées par stagiaire.
        $parStagiaire = $session->isFpc()
            ? $session->seances->groupBy('user_id')
            : null;

        return view('formateur.sessions.show', compact('session', 'parStagiaire'));
    }

    private function autoriser(SessionFormation $session): void
    {
        abort_unless(
            $session->formateur_id === auth()->id() || $session->formateurs()->whereKey(auth()->id())->exists(),
            403,
        );
    }
}
