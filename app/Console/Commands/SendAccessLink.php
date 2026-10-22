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

        // Le verrou (AppServiceProvider) a annulé l'envoi : ne pas annoncer un e-mail parti.
        if ($user->isStagiaire() && ! config('edl.envois.stagiaires')) {
            $this->warn("Envoi bloqué : les envois aux stagiaires sont désactivés (EDL_ENVOIS_STAGIAIRES=false). Aucun e-mail n'est parti vers {$user->email}.");

            return self::SUCCESS;
        }

        $this->info("Lien envoyé à {$user->email} (identifiant {$user->login}).");

        return self::SUCCESS;
    }
}
