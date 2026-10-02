<?php

namespace App\Services;

use App\Http\Requests\Formateur\SeanceRequest;
use App\Models\Referentiel;
use App\Models\Ressource;
use App\Models\Seance;
use App\Models\SessionFormation;
use App\Support\OptionsSeance;
use Illuminate\Http\UploadedFile;

/**
 * Écriture d'une fiche pédagogique (séance) : partagée entre l'espace
 * formateur (App\Http\Controllers\Formateur\SeanceController) et l'espace
 * admin (App\Http\Controllers\Admin\SeanceController), pour éviter que les
 * deux divergent sur une logique qui a valeur de justificatif pédagogique.
 */
class SeanceService
{
    public function mettreAJour(Seance $seance, SeanceRequest $request): void
    {
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
    }

    /**
     * Données communes aux formulaires de création/modification (espace
     * formateur et espace admin).
     *
     * @return array<string, mixed>
     */
    public function donneesFormulaire(SessionFormation $session, Seance $seance): array
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

    public function synchroniser(Seance $seance, SeanceRequest $request): void
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
}
