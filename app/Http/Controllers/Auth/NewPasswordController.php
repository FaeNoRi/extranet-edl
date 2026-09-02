<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Affiche le formulaire de création / réinitialisation du mot de passe.
     */
    public function create(string $token): View|RedirectResponse
    {
        if (! $this->resolveToken($token)) {
            return redirect()->route('password.request')
                ->withErrors(['login' => __('passwords.token')]);
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Enregistre le nouveau mot de passe.
     *
     * @throws ValidationException
     */
    public function store(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $resetToken = $this->resolveToken($token);

        if (! $resetToken) {
            throw ValidationException::withMessages([
                'password' => __('passwords.token'),
            ]);
        }

        $resetToken->user->forceFill([
            'password' => $request->input('password'),
            'remember_token' => null,
        ])->save();

        $resetToken->markUsed();

        return redirect()->route('login')->with('status', __('passwords.reset'));
    }

    protected function resolveToken(string $token): ?PasswordResetToken
    {
        $resetToken = PasswordResetToken::with('user')->where('token', $token)->first();

        return $resetToken && $resetToken->isValid() ? $resetToken : null;
    }
}
