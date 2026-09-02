<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StagiaireController extends Controller
{
    public function index(Request $request): View
    {
        $stagiaires = User::query()
            ->whereIn('role', [Role::StagiaireOp->value, Role::StagiaireFpc->value])
            ->with('sessionFormations:id,nom,num_GESCOF')
            ->when($request->string('q')->toString(), fn ($q, $t) => $q->where(
                fn ($w) => $w->where('nom', 'like', "%$t%")->orWhere('prenom', 'like', "%$t%")->orWhere('email', 'like', "%$t%")->orWhere('login', 'like', "%$t%")
            ))
            ->when($request->integer('session'), fn ($q, $id) => $q->whereHas('sessionFormations', fn ($s) => $s->where('session_formations.id', $id)))
            ->when($request->boolean('disparus'), fn ($q) => $q->whereHas('sessionFormations', fn ($s) => $s->whereNotNull('session_formation_user.disparu_import_at')))
            ->orderBy('nom')
            ->paginate(25)
            ->withQueryString();

        return view('admin.stagiaires.index', [
            'stagiaires' => $stagiaires,
            'sessions' => SessionFormation::orderBy('nom')->get(['id', 'nom', 'num_GESCOF']),
        ]);
    }

    public function destroy(User $stagiaire): RedirectResponse
    {
        abort_unless($stagiaire->isStagiaire(), 404);

        $nom = $stagiaire->nom_complet;
        $stagiaire->delete();

        return back()->with('succes', "Compte de « {$nom} » supprimé.");
    }
}
