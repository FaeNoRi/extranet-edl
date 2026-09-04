<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PurgeComptesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurgeController extends Controller
{
    public function index(PurgeComptesService $service): View
    {
        return view('admin.purges.index', [
            'op' => $service->comptesOpAPurger(),
            'fpc' => $service->comptesFpcAPurger(),
        ]);
    }

    public function executer(Request $request, PurgeComptesService $service): RedirectResponse
    {
        $type = $request->validate(['type' => ['required', 'in:op,fpc']])['type'];

        [$comptes, $motif] = $type === 'op'
            ? [$service->comptesOpAPurger(), 'fermeture estivale (comptes OP)']
            : [$service->comptesFpcAPurger(), 'formations FPC terminées en N-1'];

        $n = $service->supprimer($comptes, $motif);

        return redirect()->route('admin.purges.index')
            ->with('succes', $n > 0 ? "{$n} compte(s) supprimé(s)." : 'Aucun compte à supprimer.');
    }
}
