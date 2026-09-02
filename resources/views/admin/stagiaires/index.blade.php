<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Stagiaires</h2>
    </x-slot>

    <x-admin.shell active="stagiaires">
        <x-admin.card>
            <x-slot name="titre">Stagiaires ({{ $stagiaires->total() }})</x-slot>

            <form method="GET" class="mb-4 flex flex-wrap gap-3">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Nom, e-mail, identifiant…"
                       class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                <select name="session" class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                    <option value="">Toutes les sessions</option>
                    @foreach ($sessions as $s)
                        <option value="{{ $s->id }}" @selected(request('session') == $s->id)>{{ $s->num_GESCOF }} — {{ $s->nom }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="disparus" value="1" @checked(request('disparus'))
                           class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                    Absents du dernier import
                </label>
                <x-secondary-button>Filtrer</x-secondary-button>
            </form>

            @if ($stagiaires->isEmpty())
                <p class="text-sm text-gray-500">Aucun stagiaire.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-2 pr-3">Nom</th>
                                <th class="py-2 pr-3">Identifiant</th>
                                <th class="py-2 pr-3">E-mail</th>
                                <th class="py-2 pr-3">Rôle</th>
                                <th class="py-2 pr-3">Session(s)</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($stagiaires as $stagiaire)
                                <tr>
                                    <td class="py-2 pr-3 font-medium text-gray-800">{{ $stagiaire->nom_complet }}</td>
                                    <td class="py-2 pr-3 text-gray-500">{{ $stagiaire->login }}</td>
                                    <td class="py-2 pr-3 text-gray-500">{{ $stagiaire->email ?: '—' }}</td>
                                    <td class="py-2 pr-3 text-xs">{{ $stagiaire->role->label() }}</td>
                                    <td class="py-2 pr-3 text-gray-500">{{ $stagiaire->sessionFormations->pluck('num_GESCOF')->join(', ') }}</td>
                                    <td class="py-2 text-right">
                                        <form method="POST" action="{{ route('admin.stagiaires.destroy', $stagiaire) }}"
                                              onsubmit="return confirm('Supprimer ce compte ?')">
                                            @csrf @method('DELETE')
                                            <button class="text-edl-rose hover:underline">Supprimer</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $stagiaires->links() }}</div>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
