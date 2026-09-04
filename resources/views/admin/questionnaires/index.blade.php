<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Questionnaires</h2>
    </x-slot>

    <x-admin.shell active="questionnaires">
        <x-admin.card>
            <x-slot name="titre">Questionnaires ({{ $questionnaires->count() }})</x-slot>
            <x-slot name="actions">
                <a href="{{ route('admin.questionnaires.create') }}"
                   class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Nouveau questionnaire
                </a>
            </x-slot>

            @if ($questionnaires->isEmpty())
                <p class="text-sm text-gray-500">Aucun questionnaire.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-2 pr-3">Titre</th>
                                <th class="py-2 pr-3">Type</th>
                                <th class="py-2 pr-3">Portée</th>
                                <th class="py-2 pr-3">Questions</th>
                                <th class="py-2 pr-3">Réponses</th>
                                <th class="py-2 pr-3">Statut</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($questionnaires as $q)
                                <tr>
                                    <td class="py-2 pr-3 font-medium text-gray-800">{{ $q->titre }}</td>
                                    <td class="py-2 pr-3">{{ $q->type->label() }}</td>
                                    <td class="py-2 pr-3 text-gray-500">{{ $q->sessionFormation?->nom ?? 'Toutes les sessions' }}</td>
                                    <td class="py-2 pr-3">{{ $q->questions_count }}</td>
                                    <td class="py-2 pr-3">{{ $q->repondants_count }}</td>
                                    <td class="py-2 pr-3">
                                        @if ($q->actif)
                                            <span class="rounded-full bg-edl-vert-fonce/15 px-2 py-0.5 text-xs text-edl-vert-fonce">Actif</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.questionnaires.resultats', $q) }}" class="text-edl-bleu hover:underline">Résultats</a>
                                        <a href="{{ route('admin.questionnaires.edit', $q) }}" class="ml-3 text-edl-bleu hover:underline">Modifier</a>
                                        <form method="POST" action="{{ route('admin.questionnaires.destroy', $q) }}" class="ml-3 inline"
                                              onsubmit="return confirm('Supprimer ce questionnaire et ses réponses ?')">
                                            @csrf @method('DELETE')
                                            <button class="text-edl-rose hover:underline">Suppr.</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
