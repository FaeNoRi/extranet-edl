<?php

namespace App\Services;

use App\Enums\CodeProduit;
use App\Models\Questionnaire;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Indicateurs de la vue d'ensemble admin : alertes opérationnelles (ce qui
 * nécessite une action) et indicateurs de pilotage (avancement pédagogique).
 * Calculs volontairement simples (le nombre de sessions reste faible) :
 * pas d'optimisation prématurée.
 */
class TableauBordAdminService
{
    public function __construct(private PurgeComptesService $purges) {}

    /** @return array{purgesOp: int, purgesFpc: int, sessionsDecrochees: Collection<int, SessionFormation>} */
    public function alertes(int $seuilDecrochageJours = 21): array
    {
        return [
            'purgesOp' => $this->purges->comptesOpAPurger()->count(),
            'purgesFpc' => $this->purges->comptesFpcAPurger()->count(),
            'sessionsDecrochees' => $this->sessionsFpcSansSeanceRecente($seuilDecrochageJours),
        ];
    }

    /**
     * Sessions FPC en cours (non terminées) sans séance enregistrée depuis
     * plus de $seuilJours jours — signe d'un suivi à la traîne.
     *
     * @return Collection<int, SessionFormation>
     */
    public function sessionsFpcSansSeanceRecente(int $seuilJours = 21): Collection
    {
        $seuil = Carbon::now()->subDays($seuilJours);

        return SessionFormation::where('code_produit', CodeProduit::Fpc->value)
            ->with('formateur')
            ->get()
            ->filter(function (SessionFormation $session) use ($seuil) {
                if ($session->finLe()?->isPast()) {
                    return false;
                }

                $derniereSeance = $session->seances()->max('date');

                return $derniereSeance ? Carbon::parse($derniereSeance)->lt($seuil) : $session->created_at->lt($seuil);
            })
            ->values();
    }

    /**
     * Avancement des sessions FPC en cours : jours de planning actifs déjà
     * couverts par au moins une séance, vs total de jours actifs. Trié du
     * plus en retard au plus avancé.
     *
     * @return Collection<int, array{session: SessionFormation, realises: int, planifies: int, taux: ?int}>
     */
    public function avancementSessionsFpc(int $limite = 5): Collection
    {
        return SessionFormation::where('code_produit', CodeProduit::Fpc->value)
            ->with('formateur', 'jours', 'seances')
            ->get()
            ->filter(fn (SessionFormation $session) => ! $session->finLe()?->isPast())
            ->map(function (SessionFormation $session) {
                $planifies = $session->jours->where('actif', true)->count();
                $realises = $session->seances->pluck('date')->map(fn ($d) => $d->toDateString())->unique()->count();

                return [
                    'session' => $session,
                    'realises' => $realises,
                    'planifies' => $planifies,
                    'taux' => $planifies > 0 ? (int) round($realises / $planifies * 100) : null,
                ];
            })
            ->sortBy(fn ($ligne) => $ligne['taux'] ?? -1)
            ->take($limite)
            ->values();
    }

    /**
     * Questionnaires actifs et leur taux de réponse (répondants vs stagiaires
     * éligibles). Triés du taux le plus faible au plus élevé.
     *
     * @return Collection<int, array{questionnaire: Questionnaire, reponses: int, eligibles: int, taux: ?int}>
     */
    public function questionnairesTauxReponse(int $limite = 5): Collection
    {
        return Questionnaire::where('actif', true)
            ->with('sessionFormation')
            ->withCount('repondants')
            ->get()
            ->map(function (Questionnaire $questionnaire) {
                $eligibles = $questionnaire->session_formation_id
                    ? $questionnaire->sessionFormation->stagiaires()->count()
                    : User::stagiaires()->count();

                return [
                    'questionnaire' => $questionnaire,
                    'reponses' => $questionnaire->repondants_count,
                    'eligibles' => $eligibles,
                    'taux' => $eligibles > 0 ? (int) round($questionnaire->repondants_count / $eligibles * 100) : null,
                ];
            })
            ->sortBy(fn ($ligne) => $ligne['taux'] ?? -1)
            ->take($limite)
            ->values();
    }
}
