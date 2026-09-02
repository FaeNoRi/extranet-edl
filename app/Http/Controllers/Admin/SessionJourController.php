<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionFormation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SessionJourController extends Controller
{
    /**
     * Synchronise le planning d'une session : ajoute les dates saisies et met
     * à jour l'état « actif » des jours existants (décochage fériés/vacances).
     */
    public function sync(Request $request, SessionFormation $session): RedirectResponse
    {
        $data = $request->validate([
            'nouvelles_dates' => ['nullable', 'string'],
            'actifs' => ['array'],
            'actifs.*' => ['integer'],
        ]);

        // Ajout des nouvelles dates.
        foreach (preg_split('/[\s,;]+/', trim($data['nouvelles_dates'] ?? '')) ?: [] as $brut) {
            $date = $this->parseDate($brut);
            if ($date) {
                $session->jours()->firstOrCreate(['date' => $date->toDateString()], ['actif' => true]);
            }
        }

        // Mise à jour de l'état actif.
        $actifs = collect($data['actifs'] ?? [])->map('intval');
        foreach ($session->jours as $jour) {
            $jour->update(['actif' => $actifs->contains($jour->id)]);
        }

        return back()->with('succes', 'Planning mis à jour.');
    }

    private function parseDate(string $brut): ?Carbon
    {
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $brut)->startOfDay();
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
