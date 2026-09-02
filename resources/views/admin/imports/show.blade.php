<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Import GESCOF</h2>
    </x-slot>

    <x-admin.shell active="imports">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-edl-marron">
                    {{ $import->applique ? 'Import appliqué' : 'Simulation' }}
                </h1>
                <p class="text-sm text-gray-500">
                    {{ $import->fichier_nom }} · {{ $import->created_at->translatedFormat('d/m/Y à H\hi') }}
                    @if ($import->auteur) · par {{ $import->auteur->nom_complet }} @endif
                </p>
            </div>
            <a href="{{ route('admin.imports.index') }}" class="text-sm text-edl-bleu hover:underline">← Historique</a>
        </div>

        <x-admin.card>
            @include('admin.imports._rapport', ['import' => $import])
        </x-admin.card>

        @unless ($import->applique)
            <x-admin.card titre="Appliquer cet import">
                @if ($fichierPresent)
                    <p class="mb-4 text-sm text-gray-600">
                        Les {{ $import->comptes_crees }} compte(s) et {{ $import->sessions_creees }} session(s) ci-dessus
                        seront réellement créés. Cette opération est journalisée.
                    </p>
                    <form method="POST" action="{{ route('admin.imports.appliquer', $import) }}"
                          onsubmit="return confirm('Appliquer définitivement cet import ?')">
                        @csrf
                        <label class="mb-3 flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="envoyer_acces" value="1"
                                   class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                            Envoyer immédiatement le lien d'accès aux comptes créés
                        </label>
                        <x-primary-button>Appliquer l'import</x-primary-button>
                    </form>
                @else
                    <p class="text-sm text-edl-rose">
                        Le fichier de cette simulation n'est plus disponible.
                        <a href="{{ route('admin.imports.index') }}" class="underline">Relancer une simulation.</a>
                    </p>
                @endif
            </x-admin.card>
        @endunless
    </x-admin.shell>
</x-app-layout>
