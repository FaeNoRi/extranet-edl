<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReferentielRequest;
use App\Models\Referentiel;
use App\Models\Ressource;
use App\Rules\FichierAutorise;
use App\Support\CodeStage;
use App\Support\NiveauxReferentiel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReferentielController extends Controller
{
    public function index(Request $request): View
    {
        $langue = $request->string('langue')->toString();

        $entrees = Referentiel::query()
            ->withCount('ressources')
            ->when($request->string('module')->toString(), fn ($q, $m) => $q->module($m))
            ->when($langue === 'toutes', fn ($q) => $q->whereNull('langue'))
            ->when($langue !== '' && $langue !== 'toutes', fn ($q) => $q->where('langue', $langue))
            ->orderBy('module')
            ->orderBy('code')
            ->orderBy('langue')
            ->get()
            ->groupBy('module');

        return view('admin.referentiel.index', [
            'entrees' => $entrees,
            'modules' => ReferentielRequest::MODULES,
            'langues' => CodeStage::langues(),
        ]);
    }

    public function create(): View
    {
        return view('admin.referentiel.form', [
            'referentiel' => new Referentiel,
        ] + $this->options());
    }

    public function store(ReferentielRequest $request): RedirectResponse
    {
        $referentiel = Referentiel::create($request->donnees());

        return redirect()->route('admin.referentiel.edit', $referentiel)
            ->with('succes', 'Entrée du référentiel créée. Vous pouvez maintenant y joindre des documents.');
    }

    public function edit(Referentiel $referentiel): View
    {
        $referentiel->load('ressources');

        return view('admin.referentiel.form', compact('referentiel') + $this->options());
    }

    public function update(ReferentielRequest $request, Referentiel $referentiel): RedirectResponse
    {
        $referentiel->update($request->donnees());

        return redirect()->route('admin.referentiel.index')
            ->with('succes', 'Entrée du référentiel mise à jour.');
    }

    public function destroy(Referentiel $referentiel): RedirectResponse
    {
        if ($referentiel->seances()->exists()) {
            return back()->with('erreur', 'Cette entrée est utilisée dans des séances : elle ne peut pas être supprimée.');
        }

        $referentiel->load('ressources');
        DB::transaction(function () use ($referentiel) {
            foreach ($referentiel->ressources as $ressource) {
                $this->supprimerDocument($referentiel, $ressource);
            }
            $referentiel->delete();
        });

        return redirect()->route('admin.referentiel.index')
            ->with('succes', 'Entrée du référentiel supprimée.');
    }

    /**
     * Documents communs du code : déposés une fois, ils apparaissent dans chaque séance
     * qui coche ce code (dossier du formateur, ressources du stagiaire).
     */
    public function ajouterDocuments(Request $request, Referentiel $referentiel): RedirectResponse
    {
        $request->validate([
            'documents' => ['required', 'array'],
            'documents.*' => FichierAutorise::regles(),
        ]);

        foreach ($request->file('documents') as $fichier) {
            $ressource = Ressource::create([
                'nom' => pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME),
                'type_fichier' => Ressource::typeDepuisFichier($fichier),
                'chemin_fichier' => $fichier->store("referentiel/{$referentiel->id}"),
                'nom_fichier_original' => $fichier->getClientOriginalName(),
                'taille' => $fichier->getSize(),
                'uploader_id' => auth()->id(),
                'session_formation_id' => null,
            ]);
            $referentiel->ressources()->attach($ressource->id);
        }

        activity('Référentiel')->performedOn($referentiel)
            ->log(count($request->file('documents')).' document(s) ajouté(s) au code '.$referentiel->code);

        return back()->with('succes', 'Document(s) ajouté(s).');
    }

    public function retirerDocument(Referentiel $referentiel, Ressource $ressource): RedirectResponse
    {
        abort_unless($referentiel->ressources()->whereKey($ressource->id)->exists(), 404);

        $this->supprimerDocument($referentiel, $ressource);

        activity('Référentiel')->performedOn($referentiel)
            ->log("Document « {$ressource->nom} » retiré du code {$referentiel->code}");

        return back()->with('succes', 'Document retiré.');
    }

    /** Détache le document ; le fichier n'est supprimé que s'il ne sert plus nulle part. */
    private function supprimerDocument(Referentiel $referentiel, Ressource $ressource): void
    {
        $referentiel->ressources()->detach($ressource->id);

        if (! $ressource->referentiels()->exists() && ! $ressource->seances()->exists()) {
            Storage::delete($ressource->chemin_fichier);
            $ressource->delete();
        }
    }

    private function options(): array
    {
        return [
            'modules' => ReferentielRequest::MODULES,
            'niveaux' => NiveauxReferentiel::cles(),
            'langues' => CodeStage::langues(),
        ];
    }
}
