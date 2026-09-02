<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Sessions</h2>
    </x-slot>

    <x-admin.shell active="sessions">
        <x-admin.card>
            <x-slot name="titre">Sessions ({{ $sessions->total() }})</x-slot>
            <x-slot name="actions">
                <a href="{{ route('admin.sessions.create') }}"
                   class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Nouvelle session
                </a>
            </x-slot>

            <form method="GET" class="mb-4 flex flex-wrap gap-3">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Libellé, n° GESCOF…"
                       class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                <select name="produit" class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                    <option value="">Tous produits</option>
                    <option value="FPC" @selected(request('produit') === 'FPC')>FPC</option>
                    <option value="OP" @selected(request('produit') === 'OP')>OP</option>
                </select>
                <x-secondary-button>Filtrer</x-secondary-button>
            </form>

            @if ($sessions->isEmpty())
                <p class="text-sm text-gray-500">Aucune session.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-2 pr-3">N° GESCOF</th>
                                <th class="py-2 pr-3">Libellé</th>
                                <th class="py-2 pr-3">Produit</th>
                                <th class="py-2 pr-3">Client</th>
                                <th class="py-2 pr-3">Formateur</th>
                                <th class="py-2 pr-3">Stag.</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($sessions as $session)
                                <tr>
                                    <td class="py-2 pr-3 font-mono text-xs text-gray-500">{{ $session->num_GESCOF }}</td>
                                    <td class="py-2 pr-3 font-medium text-gray-800">{{ $session->nom }}</td>
                                    <td class="py-2 pr-3">
                                        <span @class([
                                            'rounded px-1.5 py-0.5 text-xs',
                                            'bg-edl-violet/15 text-edl-violet' => $session->isFpc(),
                                            'bg-edl-bleu/15 text-edl-bleu' => $session->isOp(),
                                        ])>{{ $session->code_produit->value }}</span>
                                        @if ($session->distanciel)
                                            <span class="ml-1 rounded bg-edl-orange/15 px-1.5 py-0.5 text-xs text-edl-orange">distanciel</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3 text-gray-600">{{ $session->client?->nom ?? '—' }}</td>
                                    <td class="py-2 pr-3 text-gray-600">
                                        {{ $session->formateur?->nom_complet ?? '—' }}
                                        @unless ($session->formateur_id)
                                            <span class="rounded bg-edl-rose/15 px-1.5 py-0.5 text-xs text-edl-rose">à affecter</span>
                                        @endunless
                                    </td>
                                    <td class="py-2 pr-3 text-gray-500">{{ $session->stagiaires_count }}</td>
                                    <td class="py-2 text-right">
                                        <a href="{{ route('admin.sessions.show', $session) }}" class="text-edl-bleu hover:underline">Ouvrir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $sessions->links() }}</div>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
