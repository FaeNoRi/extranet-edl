<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lien de création / réinitialisation du mot de passe affiché à l'administrateur, qui le transmet
 * lui-même : indispensable tant que les e-mails sont coupés (hébergement sans SSH, phase de test),
 * utile ensuite si un message n'arrive pas. Même jeton que l'e-mail (48 h, usage unique, les
 * liens précédents non utilisés sont invalidés). Chaque génération est journalisée.
 */
class LienAccesController extends Controller
{
    public function __invoke(Request $request, User $utilisateur): RedirectResponse
    {
        $jeton = PasswordResetToken::issueFor($utilisateur);

        activity('Accès')
            ->performedOn($utilisateur)
            ->causedBy($request->user())
            ->log('Lien de création du mot de passe généré');

        return back()->with('lien_acces', [
            'url' => route('password.setup', ['token' => $jeton->token]),
            'nom' => $utilisateur->nom_complet,
            'login' => $utilisateur->login,
        ]);
    }
}
