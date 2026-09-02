<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FormateurRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FormateurController extends Controller
{
    public function index(Request $request): View
    {
        $formateurs = User::where('role', Role::Formateur->value)
            ->when($request->string('q')->toString(), fn ($query, $q) => $query->where(
                fn ($w) => $w->where('nom', 'like', "%$q%")->orWhere('prenom', 'like', "%$q%")->orWhere('login', 'like', "%$q%")
            ))
            ->withCount('sessionsEncadrees')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('admin.formateurs.index', compact('formateurs'));
    }

    public function create(): View
    {
        return view('admin.formateurs.form', ['formateur' => new User(['formateur_op' => true])]);
    }

    public function store(FormateurRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['role'] = Role::Formateur->value;
        $data['nom'] = mb_strtoupper($data['nom']);
        $data['password'] = Str::random(40);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('formateurs', 'public');
        }

        $formateur = User::create($data);

        if ($request->boolean('envoyer_acces')) {
            $formateur->notify(new PasswordSetupLink(PasswordResetToken::issueFor($formateur), nouveauCompte: true));
        }

        return redirect()->route('admin.formateurs.index')
            ->with('succes', "Formateur « {$formateur->nom_complet} » créé.");
    }

    public function edit(User $formateur): View
    {
        abort_unless($formateur->isFormateur(), 404);

        return view('admin.formateurs.form', compact('formateur'));
    }

    public function update(FormateurRequest $request, User $formateur): RedirectResponse
    {
        abort_unless($formateur->isFormateur(), 404);

        $data = $request->validated();
        $data['nom'] = mb_strtoupper($data['nom']);

        if ($request->hasFile('photo')) {
            if ($formateur->photo_path) {
                Storage::disk('public')->delete($formateur->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('formateurs', 'public');
        }

        $formateur->update($data);

        return redirect()->route('admin.formateurs.index')
            ->with('succes', "Formateur « {$formateur->nom_complet} » mis à jour.");
    }

    public function destroy(User $formateur): RedirectResponse
    {
        abort_unless($formateur->isFormateur(), 404);

        if ($formateur->sessionsEncadrees()->exists()) {
            return back()->with('erreur', 'Ce formateur est rattaché à des sessions : retirez-le d\'abord.');
        }

        $formateur->delete();

        return redirect()->route('admin.formateurs.index')
            ->with('succes', 'Formateur archivé.');
    }
}
