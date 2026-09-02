<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Administration</h2>
    </x-slot>

    <x-admin.shell active="dashboard" titre="Vue d'ensemble">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tuiles = [
                    ['Sessions', $nbSessions, "{$nbSessionsFpc} FPC · {$nbSessionsOp} OP", 'admin.sessions.index'],
                    ['Stagiaires', $nbStagiaires, $nbStagiairesDisparus ? "{$nbStagiairesDisparus} disparu(s) de l'import" : 'à jour', 'admin.stagiaires.index'],
                    ['Formateurs', $nbFormateurs, null, 'admin.formateurs.index'],
                    ['Sessions sans référent', $sessionsSansFormateur, 'formateur à affecter', 'admin.sessions.index'],
                ];
            @endphp

            @foreach ($tuiles as [$label, $valeur, $detail, $route])
                <a href="{{ route($route) }}" class="rounded-lg bg-white p-5 shadow-sm transition hover:shadow">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-3xl font-semibold text-edl-bleu">{{ $valeur }}</p>
                    @if ($detail)
                        <p class="mt-1 text-xs text-gray-400">{{ $detail }}</p>
                    @endif
                </a>
            @endforeach
        </div>

        <x-admin.card titre="Import GESCOF">
            @if ($dernierImport)
                <p class="text-sm text-gray-600">
                    Dernier import appliqué le
                    <strong>{{ $dernierImport->created_at->translatedFormat('d/m/Y à H\hi') }}</strong>
                    ({{ $dernierImport->fichier_nom }}) —
                    {{ $dernierImport->comptes_crees }} compte(s) créé(s),
                    {{ $dernierImport->sessions_creees }} session(s) créée(s).
                </p>
            @else
                <p class="text-sm text-gray-500">Aucun import appliqué pour le moment.</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('admin.imports.index') }}"
                   class="inline-flex rounded-md bg-edl-bleu px-4 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Nouvel import
                </a>
            </div>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
