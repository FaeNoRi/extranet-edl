<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Formateurs</h2>
    </x-slot>

    <x-admin.shell active="formateurs">
        <x-admin.card>
            <x-slot name="titre">Formateurs ({{ $formateurs->total() }})</x-slot>
            <x-slot name="actions">
                <a href="{{ route('admin.formateurs.create') }}"
                   class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Nouveau formateur
                </a>
            </x-slot>

            <form method="GET" class="mb-4">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher un nom, un identifiant…"
                       class="w-full max-w-sm rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
            </form>

            @if ($formateurs->isEmpty())
                <p class="text-sm text-gray-500">Aucun formateur.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-2 pr-3">Nom</th>
                                <th class="py-2 pr-3">Identifiant</th>
                                <th class="py-2 pr-3">Interventions</th>
                                <th class="py-2 pr-3">Sessions</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($formateurs as $formateur)
                                <tr>
                                    <td class="py-2 pr-3 font-medium text-gray-800">{{ $formateur->nom_complet }}</td>
                                    <td class="py-2 pr-3 text-gray-500">{{ $formateur->login }}</td>
                                    <td class="py-2 pr-3">
                                        @if ($formateur->formateur_fpc)<span class="mr-1 rounded bg-edl-violet/15 px-1.5 py-0.5 text-xs text-edl-violet">FPC</span>@endif
                                        @if ($formateur->formateur_op)<span class="rounded bg-edl-bleu/15 px-1.5 py-0.5 text-xs text-edl-bleu">OP</span>@endif
                                    </td>
                                    <td class="py-2 pr-3 text-gray-500">{{ $formateur->sessions_encadrees_count }}</td>
                                    <td class="py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.formateurs.edit', $formateur) }}" class="text-edl-bleu hover:underline">Modifier</a>
                                        <form method="POST" action="{{ route('admin.formateurs.destroy', $formateur) }}" class="ml-3 inline"
                                              onsubmit="return confirm('Archiver ce formateur ?')">
                                            @csrf @method('DELETE')
                                            <button class="text-edl-rose hover:underline">Archiver</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $formateurs->links() }}</div>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
