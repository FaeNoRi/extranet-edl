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
            ->loadCount(['seances', 'stagiaires'])
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
