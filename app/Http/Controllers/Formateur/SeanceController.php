<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Formateur\SeanceRequest;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Services\FichePedagogiqueService;
use App\Services\SeanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeanceController extends Controller
{
    public function create(SessionFormation $session, SeanceService $seances): View
    {
        $this->autoriser($session);

        return view('formateur.seances.form', $seances->donneesFormulaire($session, new Seance([
            'session_formation_id' => $session->id,
            'date' => now()->toDateString(),
            'langue' => $session->langue,
        ])));
    }

    public function store(SeanceRequest $request, FichePedagogiqueService $fiches, SeanceService $seances): RedirectResponse
    {
        $seance = DB::transaction(function () use ($request, $seances) {
            $session = $request->sessionFormation();

            $seance = Seance::create([
                'session_formation_id' => $session->id,
                'formateur_id' => $request->user()->id,
                'user_id' => $session->isFpc() ? $request->input('user_id') : null,
                'date' => $request->date('date'),
                'langue' => $session->langue,
                'objectifs' => $request->input('objectifs', []),
                'outils' => $request->input('outils', []),
                'contenu' => $request->input('contenu'),
                'sources' => $request->input('sources'),
                'analyse_seance' => $request->input('analyse_seance'),
            ]);

            $seances->synchroniser($seance, $request);

            return $seance;
        });

        $fiches->generer($seance);

        return redirect()->route('formateur.seances.show', $seance)
            ->with('succes', 'Fiche pédagogique enregistrée.');
    }

    public function show(Seance $seance): View
    {
        $this->authorize('view', $seance);

        $seance->load([
            'sessionFormation.client', 'formateur', 'stagiaire',
            'referentiels.ressources', 'ressources',
        ]);

        return view('formateur.seances.show', compact('seance'));
    }

    public function edit(Seance $seance, SeanceService $seances): View
    {
        $this->authorize('update', $seance);

        return view('formateur.seances.form', $seances->donneesFormulaire($seance->sessionFormation, $seance));
    }

    public function update(SeanceRequest $request, Seance $seance, FichePedagogiqueService $fiches, SeanceService $seances): RedirectResponse
    {
        $this->authorize('update', $seance);

        DB::transaction(fn () => $seances->mettreAJour($seance, $request));

        $fiches->generer($seance);

        return redirect()->route('formateur.seances.show', $seance)
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

        return redirect()->route('formateur.sessions.show', $session)
            ->with('succes', 'Séance supprimée.');
    }

    private function autoriser(SessionFormation $session): void
    {
        abort_unless(
            $session->formateur_id === auth()->id() || $session->formateurs()->whereKey(auth()->id())->exists(),
            403,
        );
    }
}
