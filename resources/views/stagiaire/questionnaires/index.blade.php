<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Questionnaires</h2>
    </x-slot>

    <x-stagiaire.shell active="questionnaires">
        @if ($questionnaires->isEmpty())
            <x-admin.card><p class="text-sm text-gray-500">Aucun questionnaire pour le moment.</p></x-admin.card>
        @else
            <div class="space-y-3">
                @foreach ($questionnaires as $q)
                    <div class="flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                        <div>
                            <p class="font-medium text-gray-800">{{ $q->titre }}</p>
                            <p class="text-xs text-gray-400">{{ $q->type->label() }} · {{ $q->questions_count }} question(s)</p>
                        </div>
                        @if ($q->soumis)
                            <span class="rounded-full bg-edl-vert-fonce/15 px-3 py-1 text-xs font-medium text-edl-vert-fonce">Répondu</span>
                        @else
                            <a href="{{ route('stagiaire.questionnaires.show', $q) }}"
                               class="rounded-md bg-edl-rose px-3 py-2 text-sm font-semibold text-white hover:opacity-90">
                                Répondre
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-stagiaire.shell>
</x-app-layout>
