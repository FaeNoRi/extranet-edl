<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Purges de comptes</h2>
    </x-slot>

    <x-admin.shell active="purges" titre="Purges de comptes">
        <p class="text-sm text-gray-600">
            Suppression (récupérable) des comptes stagiaires selon le cahier des charges.
            La purge OP s'exécute aussi automatiquement chaque nuit après la fermeture estivale
            (<code>{{ config('edl.purges.op_apres') }}</code>) ; la purge FPC se déclenche ici.
        </p>

        @foreach ([
            ['op', 'Comptes OP', $op, 'Comptes OP dont toutes les sessions se sont terminées avant le 1er septembre.'],
            ['fpc', 'Comptes FPC', $fpc, 'Comptes FPC dont toutes les formations se sont terminées l\'année N-1 ou avant.'],
        ] as [$type, $titre, $comptes, $aide])
            <x-admin.card>
                <x-slot name="titre">{{ $titre }} — {{ $comptes->count() }} concerné(s)</x-slot>
                <x-slot name="actions">
                    @if ($comptes->isNotEmpty())
                        <form method="POST" action="{{ route('admin.purges.executer') }}"
                              onsubmit="return confirm('Supprimer {{ $comptes->count() }} compte(s) {{ strtoupper($type) }} ?')">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <button class="rounded-md border border-edl-rose px-3 py-2 text-sm font-semibold text-edl-rose hover:bg-edl-rose/10">
                                Purger les comptes {{ strtoupper($type) }}
                            </button>
                        </form>
                    @endif
                </x-slot>

                <p class="mb-3 text-xs text-gray-400">{{ $aide }}</p>

                @if ($comptes->isEmpty())
                    <p class="text-sm text-gray-500">Aucun compte à purger.</p>
                @else
                    <ul class="grid gap-1 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($comptes as $compte)
                            <li class="text-gray-600">{{ $compte->nom_complet }} <span class="text-gray-400">({{ $compte->login }})</span></li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        @endforeach
    </x-admin.shell>
</x-app-layout>
