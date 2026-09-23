<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReferentielRequest;
use App\Models\Referentiel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferentielController extends Controller
{
    public function index(Request $request): View
    {
        $entrees = Referentiel::query()
            ->when($request->string('module')->toString(), fn ($q, $m) => $q->module($m))
            ->orderBy('module')
            ->orderBy('code')
            ->get()
            ->groupBy('module');

        return view('admin.referentiel.index', [
            'entrees' => $entrees,
            'modules' => ReferentielRequest::MODULES,
        ]);
    }

    public function create(): View
    {
        return view('admin.referentiel.form', [
            'referentiel' => new Referentiel,
        ] + $this->options());
    }

    public function store(ReferentielRequest $request): RedirectResponse
    {
        Referentiel::create($request->validated());

        return redirect()->route('admin.referentiel.index')
            ->with('succes', 'Entrée du référentiel créée.');
    }

    public function edit(Referentiel $referentiel): View
    {
        return view('admin.referentiel.form', compact('referentiel') + $this->options());
    }

    public function update(ReferentielRequest $request, Referentiel $referentiel): RedirectResponse
    {
        $referentiel->update($request->validated());

        return redirect()->route('admin.referentiel.index')
            ->with('succes', 'Entrée du référentiel mise à jour.');
    }

    public function destroy(Referentiel $referentiel): RedirectResponse
    {
        if ($referentiel->seances()->exists()) {
            return back()->with('erreur', 'Cette entrée est utilisée dans des séances : elle ne peut pas être supprimée.');
        }

        $referentiel->delete();

        return redirect()->route('admin.referentiel.index')
            ->with('succes', 'Entrée du référentiel supprimée.');
    }

    private function options(): array
    {
        return [
            'modules' => ReferentielRequest::MODULES,
            'niveaux' => ReferentielRequest::NIVEAUX,
        ];
    }
}
