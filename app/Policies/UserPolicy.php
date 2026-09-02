<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Seul l'administrateur gère les comptes (création, modification,
     * suppression). Cf. cahier des charges : « Aucune suppression possible
     * pour personne, sauf pour les admins ».
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->is($model);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && ! $user->is($model);
    }
}
