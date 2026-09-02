<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Affiche le formulaire de demande de lien.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoie un lien de (ré)initialisation de mot de passe.
     *
     * La demande se fait par identifiant : une même adresse e-mail peut être
     * rattachée à plusieurs comptes (1 accès = 1 session).
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['login' => ['required', 'string']]);

        $key = 'password-reset:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'login' => __('passwords.throttled'),
            ]);
        }

        RateLimiter::hit($key, 60);

        $user = User::where('login', $request->string('login'))->first();

        if ($user) {
            $token = PasswordResetToken::issueFor($user);
            $user->notify(new PasswordSetupLink($token));
        }

        // Réponse générique : ne pas révéler l'existence d'un compte.
        return back()->with('status', __('passwords.sent'));
    }
}
