<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Ressources pédagogiques</h2>
    </x-slot>

    <x-stagiaire.shell active="ressources">
        <p class="text-sm text-gray-600">
            Un dossier par séance, du plus récent au plus ancien. Chaque dossier contient les documents
            partagés par votre formateur et les fiches du référentiel des thèmes abordés.
        </p>

        @if ($dossiers->isEmpty())
            <x-admin.card><p class="text-sm text-gray-500">Aucune séance réalisée pour le moment.</p></x-admin.card>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($dossiers as $seance)
                    <a href="{{ route('stagiaire.ressources.show', $seance) }}"
                       class="rounded-lg bg-white p-4 shadow-sm transition hover:shadow">
                        <p class="text-lg font-semibold tabular-nums text-edl-marron">{{ $seance->date->format('d/m/Y') }}</p>
                        <p class="mt-1 text-xs text-gray-400">{{ $seance->date->translatedFormat('l') }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </x-stagiaire.shell>
</x-app-layout>
