<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Formateur\SeanceRequest;
use App\Models\Seance;
use App\Services\FichePedagogiqueService;
use App\Services\SeanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Consultation/modification d'une fiche pédagogique (séance) depuis
 * l'administration — création réservée au formateur (App\Http\Controllers\
 * Formateur\SeanceController::create/store), qui attribue la séance à son
 * auteur. La logique d'écriture est partagée via App\Services\SeanceService.
 */
class SeanceController extends Controller
{
    public function show(Seance $seance): View
    {
        $this->authorize('view', $seance);

        $seance->load([
            'sessionFormation.client', 'formateur', 'stagiaire',
            'referentiels.ressources', 'ressources',
        ]);

        return view('admin.seances.show', compact('seance'));
    }

    public function edit(Seance $seance, SeanceService $seances): View
    {
        $this->authorize('update', $seance);

        return view('admin.seances.form', $seances->donneesFormulaire($seance->sessionFormation, $seance));
    }

    public function update(SeanceRequest $request, Seance $seance, FichePedagogiqueService $fiches, SeanceService $seances): RedirectResponse
    {
        $this->authorize('update', $seance);

        DB::transaction(fn () => $seances->mettreAJour($seance, $request));

        $fiches->generer($seance);

        return redirect()->route('admin.seances.show', $seance)
            ->with('succes', 'Fiche pédagogique mise à jour.');
    }

    public function fiche(Seance $seance, FichePedagogiqueService $fiches): StreamedResponse
    {
        $this->authorize('view', $seance);

        if (! $seance->fiche_pdf_path || ! Storage::exists($seance->fiche_pdf_path)) {
            $fiches->generer($seance);
        }

        return Storage::download(
            $seance->fiche_pdf_path,
            'fiche-pedagogique-'.$seance->date->format('Y-m-d').'.pdf',
        );
    }

    public function destroy(Seance $seance): RedirectResponse
    {
        $this->authorize('delete', $seance);

        $session = $seance->sessionFormation;
        $seance->delete();

        return redirect()->route('admin.sessions.show', $session)
            ->with('succes', 'Séance supprimée.');
    }
}
