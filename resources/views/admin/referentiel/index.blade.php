<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Référentiel</h2>
    </x-slot>

    <x-admin.shell active="referentiel">
        <x-admin.card>
            <x-slot name="titre">Référentiel ({{ $entrees->flatten()->count() }})</x-slot>
            <x-slot name="actions">
                <a href="{{ route('admin.referentiel.create') }}"
                   class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Nouvelle entrée
                </a>
            </x-slot>

            <form method="GET" class="mb-4 flex flex-wrap items-center gap-3">
                <select name="module" onchange="this.form.requestSubmit()" class="rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                    <option value="">Tous les modules</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}" @selected(request('module') === $m)>{{ $m }}</option>
                    @endforeach
                </select>
                <x-secondary-button type="submit">Filtrer</x-secondary-button>
                @if (request()->filled('module'))
                    <a href="{{ route('admin.referentiel.index') }}" class="text-sm text-gray-500 hover:text-edl-bleu hover:underline">Réinitialiser</a>
                @endif
            </form>

            @if ($entrees->isEmpty())
                <p class="text-sm text-gray-500">Aucune entrée.</p>
            @else
                <div class="space-y-6">
                    @foreach ($entrees as $module => $groupe)
                        <div>
                            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-edl-marron">{{ $module }}</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                                        <tr>
                                            <th class="py-2 pr-3">Code</th>
                                            <th class="py-2 pr-3">Contenu</th>
                                            <th class="py-2 pr-3">Niveaux</th>
                                            <th class="py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach ($groupe as $entree)
                                            <tr>
                                                <td class="py-2 pr-3 font-mono text-xs text-gray-500">{{ $entree->code }}</td>
                                                <td class="py-2 pr-3 text-gray-800">{{ $entree->contenu }}</td>
                                                <td class="py-2 pr-3 text-gray-500">{{ implode(', ', $entree->niveaux) ?: '—' }}</td>
                                                <td class="py-2 text-right whitespace-nowrap">
                                                    <a href="{{ route('admin.referentiel.edit', $entree) }}" class="text-edl-bleu hover:underline">Modifier</a>
                                                    <form method="POST" action="{{ route('admin.referentiel.destroy', $entree) }}" class="ml-3 inline"
                                                          onsubmit="return confirm('Supprimer cette entrée du référentiel ?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-edl-rose hover:underline">Supprimer</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
