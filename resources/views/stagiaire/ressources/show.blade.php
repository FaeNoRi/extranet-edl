@php
    $session = $seance->sessionFormation;
    $emargement = $session->distanciel
        ? \App\Models\Emargement::where('seance_id', $seance->id)->where('user_id', auth()->id())->first()
        : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Séance du {{ $seance->date->format('d/m/Y') }}</h2>
    </x-slot>

    <x-stagiaire.shell active="ressources">
        <a href="{{ route('stagiaire.ressources.index') }}" class="text-sm text-edl-bleu hover:underline">← Toutes les séances</a>

        @if ($session->distanciel)
            <x-admin.card titre="Émargement">
                @if ($emargement?->present)
                    <p class="text-sm text-edl-vert-fonce">
                        ✓ Émargé le {{ $emargement->signe_at->format('d/m/Y à H\hi') }}.
                    </p>
                @else
                    <form method="POST" action="{{ route('stagiaire.emargement', $seance) }}">
                        @csrf
                        <p class="mb-3 text-sm text-gray-600">Confirmez votre présence à cette séance.</p>
                        <x-primary-button>J'émarge</x-primary-button>
                    </form>
                @endif
            </x-admin.card>
        @endif

        <div class="grid gap-4 lg:grid-cols-2"
             x-data="{ apercu: null, titre: null }">

            <div class="space-y-4">
                <x-admin.card titre="Documents de la séance">
                    @if ($ressourcesTransmises->isEmpty())
                        <p class="text-sm text-gray-500">Aucun document partagé pour cette séance.</p>
                    @else
                        <ul class="divide-y divide-gray-100 text-sm">
                            @foreach ($ressourcesTransmises as $ressource)
                                <li class="flex items-center justify-between py-2">
                                    <button type="button"
                                            @click="apercu='{{ route('stagiaire.ressources.download', $ressource) }}?apercu=1'; titre='{{ addslashes($ressource->nom) }}'"
                                            class="text-left text-edl-bleu hover:underline">
                                        {{ $ressource->nom }}
                                        <span class="text-xs text-gray-400">· {{ $ressource->type_fichier }}</span>
                                    </button>
                                    <a href="{{ route('stagiaire.ressources.download', $ressource) }}" class="text-xs text-gray-400 hover:text-edl-bleu">↓</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.card>

                <x-admin.card titre="Fiches du référentiel">
                    @if ($fichesReferentiel->isEmpty())
                        <p class="text-sm text-gray-500">Aucune fiche associée.</p>
                    @else
                        <ul class="space-y-2 text-sm">
                            @foreach ($fichesReferentiel as $referentiel)
                                <li>
                                    <p class="font-medium text-gray-800">
                                        {{ $seance->date->format('d/m/Y') }}.{{ $referentiel->contenu }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $referentiel->module }} — {{ implode('/', $referentiel->niveaux ?: []) ?: 'tous niveaux' }}</p>
                                    @foreach ($referentiel->ressources as $ressource)
                                        <button type="button"
                                                @click="apercu='{{ route('stagiaire.ressources.download', $ressource) }}?apercu=1'; titre='{{ addslashes($ressource->nom) }}'"
                                                class="mt-0.5 block text-left text-xs text-edl-bleu hover:underline">
                                            ↳ {{ $ressource->nom }}
                                        </button>
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.card>
            </div>

            {{-- Volet de visualisation --}}
            <div class="lg:sticky lg:top-6 lg:h-[70vh]">
                <div class="flex h-full flex-col rounded-lg bg-white shadow-sm">
                    <p class="border-b border-gray-100 px-4 py-2 text-sm font-medium text-gray-600"
                       x-text="titre || 'Aperçu'"></p>
                    <template x-if="apercu">
                        <iframe :src="apercu" class="min-h-[300px] flex-1 rounded-b-lg" title="Aperçu du document"></iframe>
                    </template>
                    <template x-if="!apercu">
                        <div class="flex flex-1 items-center justify-center p-6 text-center text-sm text-gray-400">
                            Sélectionnez un document pour l'afficher ici.
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </x-stagiaire.shell>
</x-app-layout>
