<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Purges de comptes stagiaires (cahier des charges §1.2).
 *
 *  - OP : comptes dont toutes les sessions se sont terminées avant le début
 *    de l'année scolaire en cours (1er septembre). Purge automatique après la
 *    fermeture estivale.
 *  - FPC : comptes dont toutes les formations se sont terminées l'année N-1
 *    ou avant. Purge au 31/12 (déclenchée par l'admin, ou proposée par la
 *    tâche planifiée).
 *
 * La suppression est un « soft delete » (récupérable, tracé au journal).
 */
class PurgeComptesService
{
    /**
     * @return Collection<int, User>
     */
    public function comptesOpAPurger(?Carbon $reference = null): Collection
    {
        $reference ??= Carbon::now();
        $debutAnneeScolaire = Carbon::create($reference->month >= 9 ? $reference->year : $reference->year - 1, 9, 1);

        return $this->stagiairesDontToutesLesSessionsSontTermineesAvant(Role::StagiaireOp, $debutAnneeScolaire);
    }

    /**
     * @return Collection<int, User>
     */
    public function comptesFpcAPurger(?Carbon $reference = null): Collection
    {
        $reference ??= Carbon::now();
        $finNMoins1 = Carbon::create($reference->year - 1, 12, 31)->endOfDay();

        return $this->stagiairesDontToutesLesSessionsSontTermineesAvant(Role::StagiaireFpc, $finNMoins1->copy()->addSecond());
    }

    /**
     * Supprime (soft delete) une collection de comptes et journalise l'opération.
     *
     * @param  Collection<int, User>  $comptes
     */
    public function supprimer(Collection $comptes, string $motif): int
    {
        if ($comptes->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($comptes) {
            User::whereIn('id', $comptes->pluck('id'))->get()->each->delete();
        });

        activity('Purge')
            ->withProperties([
                'motif' => $motif,
                'comptes' => $comptes->map(fn (User $u) => $u->login)->values()->all(),
            ])
            ->log("Purge de {$comptes->count()} compte(s) : {$motif}");

        return $comptes->count();
    }

    /**
     * @return Collection<int, User>
     */
    private function stagiairesDontToutesLesSessionsSontTermineesAvant(Role $role, Carbon $limite): Collection
    {
        return User::where('role', $role->value)
            ->with('sessionFormations.jours', 'sessionFormations.seances')
            ->get()
            ->filter(function (User $user) use ($limite) {
                if ($user->sessionFormations->isEmpty()) {
                    return false;
                }

                return $user->sessionFormations->every(
                    fn ($session) => ($fin = $session->finLe()) !== null && $fin->lt($limite)
                );
            })
            ->values();
    }
}
