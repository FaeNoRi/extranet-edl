<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Création d'un compte administrateur sans passer par les données de démonstration (qui
 * contiennent un compte « admin » au mot de passe connu et ne doivent jamais être chargées
 * en production). Le mot de passe est choisi par la personne via le lien reçu par e-mail.
 */
class CreerAdministrateur extends Command
{
    protected $signature = 'edl:creer-admin {login : Identifiant de connexion}
                            {email : Adresse e-mail qui recevra le lien de création du mot de passe}
                            {nom : Nom de famille}
                            {prenom : Prénom}';

    protected $description = 'Crée un compte administrateur et envoie le lien de création de son mot de passe';

    public function handle(): int
    {
        $login = (string) $this->argument('login');

        if (User::withTrashed()->where('login', $login)->exists()) {
            $this->error("L'identifiant « {$login} » existe déjà.");

            return self::FAILURE;
        }

        if (! filter_var($this->argument('email'), FILTER_VALIDATE_EMAIL)) {
            $this->error('Adresse e-mail invalide.');

            return self::FAILURE;
        }

        // Mot de passe aléatoire inconnu de tous : le compte n'est utilisable qu'après le lien.
        $admin = User::create([
            'login' => $login,
            'email' => $this->argument('email'),
            'nom' => mb_strtoupper((string) $this->argument('nom')),
            'prenom' => $this->argument('prenom'),
            'role' => Role::Admin,
            'password' => Hash::make(Str::random(64)),
        ]);

        $admin->notify(new PasswordSetupLink(PasswordResetToken::issueFor($admin), nouveauCompte: true));

        $this->info("Administrateur « {$login} » créé : un lien de création du mot de passe a été envoyé à {$admin->email}.");

        return self::SUCCESS;
    }
}
