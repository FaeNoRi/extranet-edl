<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdministrateurRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Comptes administrateurs : sans SSH, plus de commande edl:creer-admin sur le serveur. Le premier
 * compte vient du script SQL d'installation ; les comptes nominatifs se créent ici, puis le compte
 * d'installation est archivé.
 */
class AdministrateurController extends Controller
{
    public function index(): View
    {
        return view('admin.administrateurs.index', [
            'administrateurs' => User::where('role', Role::Admin->value)->orderBy('nom')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.administrateurs.form', ['administrateur' => new User]);
    }

    public function store(AdministrateurRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Mot de passe aléatoire inconnu de tous : le compte n'est utilisable qu'après le lien.
        $administrateur = User::create([
            ...$data,
            'nom' => mb_strtoupper($data['nom']),
            'role' => Role::Admin->value,
            'password' => Str::random(64),
        ]);

        if ($request->boolean('envoyer_acces')) {
            $administrateur->notify(new PasswordSetupLink(PasswordResetToken::issueFor($administrateur), nouveauCompte: true));
        }

        return redirect()->route('admin.administrateurs.index')
            ->with('succes', "Administrateur « {$administrateur->nom_complet} » créé. Transmettez-lui son lien d'accès.");
    }

    public function edit(User $administrateur): View
    {
        abort_unless($administrateur->isAdmin(), 404);

        return view('admin.administrateurs.form', compact('administrateur'));
    }

    public function update(AdministrateurRequest $request, User $administrateur): RedirectResponse
    {
        abort_unless($administrateur->isAdmin(), 404);

        $data = $request->validated();
        $administrateur->update([...$data, 'nom' => mb_strtoupper($data['nom'])]);

        return redirect()->route('admin.administrateurs.index')
            ->with('succes', "Administrateur « {$administrateur->nom_complet} » mis à jour.");
    }

    public function destroy(Request $request, User $administrateur): RedirectResponse
    {
        abort_unless($administrateur->isAdmin(), 404);

        // Archiver son propre compte couperait l'accès en cours, et pourrait ne laisser aucun administrateur.
        if ($administrateur->is($request->user())) {
            return back()->with('erreur', 'Vous ne pouvez pas archiver votre propre compte : demandez-le à un autre administrateur.');
        }

        $administrateur->delete();

        return redirect()->route('admin.administrateurs.index')
            ->with('succes', "Administrateur « {$administrateur->nom_complet} » archivé.");
    }
}
