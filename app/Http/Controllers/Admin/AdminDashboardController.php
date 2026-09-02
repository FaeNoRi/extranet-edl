<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CodeProduit;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\GescofImport;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'nbSessions' => SessionFormation::count(),
            'nbSessionsFpc' => SessionFormation::where('code_produit', CodeProduit::Fpc->value)->count(),
            'nbSessionsOp' => SessionFormation::where('code_produit', CodeProduit::Op->value)->count(),
            'nbFormateurs' => User::where('role', Role::Formateur->value)->count(),
            'nbStagiaires' => User::whereIn('role', [Role::StagiaireOp->value, Role::StagiaireFpc->value])->count(),
            'nbStagiairesDisparus' => User::whereHas('sessionFormations', fn ($q) => $q->whereNotNull('session_formation_user.disparu_import_at'))->count(),
            'dernierImport' => GescofImport::where('applique', true)->latest()->first(),
            'sessionsSansFormateur' => SessionFormation::whereNull('formateur_id')->count(),
        ]);
    }
}
