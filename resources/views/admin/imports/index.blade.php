<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Import GESCOF</h2>
    </x-slot>

    <x-admin.shell active="imports" titre="Import GESCOF">
        <x-admin.card titre="Nouvel import (simulation)">
            <p class="mb-4 text-sm text-gray-600">
                Fichier <code>.xlsx</code> ou <code>.csv</code> de l'export GESCOF. Colonnes attendues :
                <code>Nom, Prenom, NomClient, CodeProduit, LibelleStage, NumSession, Email, ListeItv, AccesPlateforme</code>.
                La simulation n'écrit rien — vous validez le rapport avant d'appliquer.
            </p>

            <form method="POST" action="{{ route('admin.imports.simuler') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <x-input-label for="fichier" :value="__('Fichier GESCOF')" />
                    <input id="fichier" name="fichier" type="file" accept=".xlsx,.csv,.txt" required
                           class="mt-1 block text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-edl-bleu file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-edl-vert-fonce" />
                    <x-input-error :messages="$errors->get('fichier')" class="mt-1" />
                </div>
                <x-primary-button>Lancer la simulation</x-primary-button>
            </form>
        </x-admin.card>

        <x-admin.card titre="Historique des imports">
            @if ($imports->isEmpty())
                <p class="text-sm text-gray-500">Aucun import.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="py-2 pr-3">Date</th>
                                <th class="py-2 pr-3">Fichier</th>
                                <th class="py-2 pr-3">Type</th>
                                <th class="py-2 pr-3">Comptes</th>
                                <th class="py-2 pr-3">Sessions</th>
                                <th class="py-2 pr-3">Anomalies</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($imports as $import)
                                <tr>
                                    <td class="py-2 pr-3 whitespace-nowrap">{{ $import->created_at->format('d/m/Y H\hi') }}</td>
                                    <td class="py-2 pr-3">{{ $import->fichier_nom }}</td>
                                    <td class="py-2 pr-3">
                                        @if ($import->applique)
                                            <span class="rounded-full bg-edl-vert-fonce/15 px-2 py-0.5 text-xs text-edl-vert-fonce">Appliqué</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">Simulation</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-3">{{ $import->comptes_crees }}</td>
                                    <td class="py-2 pr-3">{{ $import->sessions_creees }}</td>
                                    <td class="py-2 pr-3">{{ count($import->anomalies ?? []) }}</td>
                                    <td class="py-2 text-right">
                                        <a href="{{ route('admin.imports.show', $import) }}" class="text-edl-bleu hover:underline">Voir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $imports->links() }}</div>
            @endif
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
