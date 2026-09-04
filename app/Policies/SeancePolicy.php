<?php

namespace App\Policies;

use App\Models\Seance;
use App\Models\SessionFormation;
use App\Models\User;

class SeancePolicy
{
    /**
     * Un formateur n'accède qu'aux séances des sessions qu'il encadre
     * (référent ou équipe). L'admin a tous les droits (Gate::before).
     */
    public function view(User $user, Seance $seance): bool
    {
        return $this->encadre($user, $seance->sessionFormation);
    }

    public function create(User $user): bool
    {
        return $user->isFormateur();
    }

    public function update(User $user, Seance $seance): bool
    {
        return $this->encadre($user, $seance->sessionFormation);
    }

    public function delete(User $user, Seance $seance): bool
    {
        return $this->encadre($user, $seance->sessionFormation);
    }

    public function encadre(User $user, ?SessionFormation $session): bool
    {
        if (! $session || ! $user->isFormateur()) {
            return false;
        }

        return $session->formateur_id === $user->id
            || $session->formateurs()->whereKey($user->id)->exists();
    }
}
