<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Génère un identifiant de connexion unique à partir d'un prénom et d'un nom.
 * Format : prenom.nom, suffixé d'un compteur en cas de collision.
 */
class LoginGenerator
{
    public function generer(string $prenom, string $nom): string
    {
        $base = Str::slug($prenom.'.'.$nom, '.');
        $base = trim($base, '.') ?: 'stagiaire';

        $login = $base;
        $i = 1;

        while (User::withTrashed()->where('login', $login)->exists()) {
            $login = $base.$i;
            $i++;
        }

        return $login;
    }
}
