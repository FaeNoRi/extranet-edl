<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Seance;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $sessions = auth()->user()->sessionsPourFormateur()
            ->loadCount([
                'seances',
                'stagiaires',
                // Avancement de la carte : jours de planning actifs passés / prévus.
                'jours as jours_prevus_count' => fn ($q) => $q->where('actif', true),
                'jours as jours_realises_count' => fn ($q) => $q->where('actif', true)->whereDate('date', '<=', today()),
            ])
            ->sortByDesc('gescof_importe_at');

        $prochainesSeances = Seance::whereIn('session_formation_id', $sessions->pluck('id'))
            ->where('date', '>=', now()->subWeek())
            ->with('sessionFormation', 'stagiaire')
            ->orderBy('date')
            ->limit(10)
            ->get();

        return view('formateur.dashboard', compact('sessions', 'prochainesSeances'));
    }
}
