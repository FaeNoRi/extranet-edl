<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Formateur\SeanceRequest;
use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Services\FichePedagogiqueService;
use App\Support\OptionsSeance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SeanceController extends Controller
{
    public function create(SessionFormation $session): View
    {
        $this->autoriser($session);

        return view('formateur.seances.form', $this->donneesFormulaire($session, new Seance([
            'session_formation_id' => $session->id,
            'date' => now()->toDateString(),
            'langue' => $session->langue,
        ])));
    }

    public function store(SeanceRequest $request, FichePedagogiqueService $fiches): RedirectResponse
    {
        $seance = DB::transaction(function () use ($request) {
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

            $this->synchroniser($seance, $request);

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

    public function edit(Seance $seance): View
    {
        $this->authorize('update', $seance);

        return view('formateur.seances.form', $this->donneesFormulaire($seance->sessionFormation, $seance));
    }

    public function update(SeanceRequest $request, Seance $seance, FichePedagogiqueService $fiches): RedirectResponse
    {
        $this->authorize('update', $seance);

        DB::transaction(function () use ($request, $seance) {
            $session = $request->sessionFormation();

            $seance->update([
                'user_id' => $session->isFpc() ? $request->input('user_id') : null,
                'date' => $request->date('date'),
                'objectifs' => $request->input('objectifs', []),
                'outils' => $request->input('outils', []),
                'contenu' => $request->input('contenu'),
                'sources' => $request->input('sources'),
                'analyse_seance' => $request->input('analyse_seance'),
            ]);

            $this->synchroniser($seance, $request);
        });

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

    private function synchroniser(Seance $seance, SeanceRequest $request): void
    {
        $seance->referentiels()->sync($request->input('referentiels', []));

        // Ressources existantes de la session (transmises).
        $existantes = collect($request->input('ressources', []))
            ->mapWithKeys(fn ($id) => [$id => ['transmis' => true]]);
        $seance->ressources()->sync($existantes);

        // Nouveaux fichiers.
        foreach ($request->file('fichiers_transmis', []) as $fichier) {
            $seance->ressources()->attach($this->creerRessource($seance, $fichier)->id, ['transmis' => true]);
        }
        foreach ($request->file('fichiers_internes', []) as $fichier) {
            $seance->ressources()->attach($this->creerRessource($seance, $fichier)->id, ['transmis' => false]);
        }
    }

    private function creerRessource(Seance $seance, UploadedFile $fichier): Ressource
    {
        return Ressource::create([
            'nom' => pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME),
            'type_fichier' => $this->typeFichier($fichier),
            'chemin_fichier' => $fichier->store("seances/{$seance->id}/ressources"),
            'nom_fichier_original' => $fichier->getClientOriginalName(),
            'taille' => $fichier->getSize(),
            'uploader_id' => auth()->id(),
            'session_formation_id' => $seance->session_formation_id,
        ]);
    }

    private function typeFichier(UploadedFile $fichier): string
    {
        return match (true) {
            str_starts_with((string) $fichier->getMimeType(), 'audio/') => 'audio',
            str_starts_with((string) $fichier->getMimeType(), 'video/') => 'video',
            str_starts_with((string) $fichier->getMimeType(), 'image/') => 'image',
            $fichier->getClientOriginalExtension() === 'pdf' => 'pdf',
            default => 'autre',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function donneesFormulaire(SessionFormation $session, Seance $seance): array
    {
        return [
            'session' => $session,
            'seance' => $seance,
            'objectifsProposes' => OptionsSeance::objectifsPour($session),
            'outils' => OptionsSeance::OUTILS,
            'modules' => Referentiel::orderBy('module')->orderBy('code')->get()->groupBy('module'),
            'ressourcesSession' => Ressource::where('session_formation_id', $session->id)->orderBy('nom')->get(),
            'stagiaires' => $session->stagiaires()->orderBy('nom')->get(),
        ];
    }

    private function autoriser(SessionFormation $session): void
    {
        abort_unless(
            $session->formateur_id === auth()->id() || $session->formateurs()->whereKey(auth()->id())->exists(),
            403,
        );
    }
}
