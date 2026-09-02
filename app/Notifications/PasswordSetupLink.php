<?php

namespace App\Notifications;

use App\Models\PasswordResetToken;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordSetupLink extends Notification
{
    use Queueable;

    public function __construct(
        public PasswordResetToken $token,
        public bool $nouveauCompte = false,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.setup', ['token' => $this->token->token]);

        $mail = (new MailMessage)
            ->subject($this->nouveauCompte
                ? 'Votre accès à l\'extranet EDL+'
                : 'Réinitialisation de votre mot de passe EDL+');

        if ($this->nouveauCompte) {
            $mail->greeting('Bonjour '.$notifiable->prenom.',')
                ->line('Un accès à l\'extranet de suivi de l\'École des Langues Grand Calais vient d\'être créé pour vous.')
                ->line('**Votre identifiant de connexion : '.$notifiable->login.'**')
                ->line('Pour activer votre compte, définissez votre mot de passe en cliquant sur le bouton ci-dessous.')
                ->action('Créer mon mot de passe', $url);
        } else {
            $mail->greeting('Bonjour '.$notifiable->prenom.',')
                ->line('Vous avez demandé à réinitialiser le mot de passe associé à l\'identifiant **'.$notifiable->login.'**.')
                ->action('Choisir un nouveau mot de passe', $url);
        }

        return $mail
            ->line('Ce lien est valable 48 heures.')
            ->line('Si vous n\'êtes pas à l\'origine de cette demande, vous pouvez ignorer cet e-mail.')
            ->salutation('L\'équipe de l\'École des Langues Grand Calais');
    }
}
