<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CodeProduit;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SessionFormationRequest;
use App\Models\Client;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionFormationController extends Controller
{
    public function index(Request $request): View
    {
        $sessions = SessionFormation::query()
            ->with('client', 'formateur')
            ->withCount(['stagiaires', 'seances'])
            ->when($request->string('produit')->toString(), fn ($q, $p) => $q->where('code_produit', $p))
            ->when($request->string('q')->toString(), fn ($q, $terme) => $q->where(
                fn ($w) => $w->where('nom', 'like', "%$terme%")->orWhere('num_GESCOF', 'like', "%$terme%")
            ))
            ->orderByDesc('gescof_importe_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sessions.index', compact('sessions'));
    }

    public function create(): View
    {
        return view('admin.sessions.form', [
            'session' => new SessionFormation(['langue' => 'Anglais', 'code_produit' => CodeProduit::Op]),
        ] + $this->options());
    }

    public function store(SessionFormationRequest $request): RedirectResponse
    {
        $session = new SessionFormation;
        $this->remplir($session, $request);

        return redirect()->route('admin.sessions.show', $session)
            ->with('succes', 'Session créée.');
    }

    public function show(SessionFormation $session): View
    {
        $session->load([
            'client', 'formateur', 'formateurs',
            'stagiaires' => fn ($q) => $q->orderBy('nom'),
            'jours' => fn ($q) => $q->orderBy('date'),
            'seances',
        ]);

        return view('admin.sessions.show', compact('session'));
    }

    public function edit(SessionFormation $session): View
    {
        $session->load('formateurs');

        return view('admin.sessions.form', compact('session') + $this->options());
    }

    public function update(SessionFormationRequest $request, SessionFormation $session): RedirectResponse
    {
        $this->remplir($session, $request);

        return redirect()->route('admin.sessions.show', $session)
            ->with('succes', 'Session mise à jour.');
    }

    public function destroy(SessionFormation $session): RedirectResponse
    {
        $session->delete();

        return redirect()->route('admin.sessions.index')
            ->with('succes', 'Session supprimée.');
    }

    private function remplir(SessionFormation $session, SessionFormationRequest $request): void
    {
        $data = $request->validated();

        if (! empty($data['nouveau_client'])) {
            $data['client_id'] = Client::firstOrCreate(['nom' => $data['nouveau_client']])->id;
        }

        $produit = CodeProduit::from($data['code_produit']);
        $data['rythme_op'] = $produit === CodeProduit::Op ? $data['rythme_op'] : null;
        if ($produit === CodeProduit::Fpc) {
            $data['distanciel'] = $request->boolean('distanciel');
        }

        $formateurs = collect($data['formateurs'] ?? []);
        unset($data['formateurs'], $data['nouveau_client']);

        $session->fill($data)->save();

        // L'équipe inclut toujours le référent.
        if ($session->formateur_id) {
            $formateurs->push($session->formateur_id);
        }
        $session->formateurs()->sync(
            $formateurs->unique()->mapWithKeys(fn ($id) => [$id => ['principal' => $id == $session->formateur_id]])
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'clients' => Client::orderBy('nom')->get(),
            'formateurs' => User::where('role', Role::Formateur->value)->orderBy('nom')->get(),
            'produits' => CodeProduit::cases(),
        ];
    }
}
