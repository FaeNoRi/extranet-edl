<?php

namespace App\Http\Controllers\Stagiaire;

use App\Http\Controllers\Controller;
use App\Models\Emargement;
use App\Models\Seance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;

/**
 * Émargement en ligne du stagiaire (FPC en distanciel uniquement).
 */
class EmargementController extends Controller
{
    public function sign(Seance $seance): RedirectResponse
    {
        $session = auth()->user()->sessionStagiaire();

        abort_unless(
            $session
            && $session->id === $seance->session_formation_id
            && $session->distanciel
            && $seance->date->lte(Carbon::today())
            && (is_null($seance->user_id) || $seance->user_id === auth()->id()),
            403,
        );

        Emargement::updateOrCreate(
            ['seance_id' => $seance->id, 'user_id' => auth()->id()],
            ['present' => true, 'signe_at' => Carbon::now()],
        );

        return back()->with('succes', 'Émargement enregistré.');
    }
}
