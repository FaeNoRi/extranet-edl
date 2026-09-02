<?php

namespace App\Console\Commands;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Console\Command;

class SendAccessLink extends Command
{
    protected $signature = 'edl:acces {login : Identifiant du compte}
                            {--nouveau : Formuler le message comme une création de compte}';

    protected $description = 'Envoie à un utilisateur un lien de création / réinitialisation de mot de passe';

    public function handle(): int
    {
        $user = User::where('login', $this->argument('login'))->first();

        if (! $user) {
            $this->error("Aucun compte pour l'identifiant « {$this->argument('login')} ».");

            return self::FAILURE;
        }

        $token = PasswordResetToken::issueFor($user);
        $user->notify(new PasswordSetupLink($token, nouveauCompte: (bool) $this->option('nouveau')));

        $this->info("Lien envoyé à {$user->email} (identifiant {$user->login}).");

        return self::SUCCESS;
    }
}
