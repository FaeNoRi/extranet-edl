@php
    $session = $seance->sessionFormation;
    $emargement = $session->distanciel
        ? \App\Models\Emargement::where('seance_id', $seance->id)->where('user_id', auth()->id())->first()
        : null;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-edl-marron">Séance du {{ $seance->date->format('d/m/Y') }}</h1>
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

        {{-- OP : consultation seule dans le lecteur durci plein page (aucun volet, aucun téléchargement). --}}
        @php $op = auth()->user()->isStagiaireOp(); @endphp

        <div @class(['grid gap-4', 'lg:grid-cols-2' => ! $op])
             x-data="{ apercu: null, titre: null, type: null }">

            <div class="space-y-4">
                <x-admin.card titre="Documents de la séance">
                    @if ($ressourcesTransmises->isEmpty())
                        <p class="text-sm text-gray-500">Aucun document partagé pour cette séance.</p>
                    @else
                        <ul class="divide-y divide-gray-100 text-sm">
                            @foreach ($ressourcesTransmises as $ressource)
                                <li class="flex items-center justify-between py-2">
                                    @if ($op)
                                        <a href="{{ route('stagiaire.ressources.apercu', $ressource) }}"
                                           class="text-left text-edl-bleu hover:underline">
                                            {{ $ressource->nom }}
                                            <span class="text-xs text-gray-500">· {{ $ressource->type_fichier }}</span>
                                        </a>
                                    @else
                                        <button type="button"
                                                @click="apercu='{{ route('stagiaire.ressources.download', $ressource) }}?apercu=1'; titre='{{ addslashes($ressource->nom) }}'; type='{{ $ressource->type_fichier }}'"
                                                class="text-left text-edl-bleu hover:underline">
                                            {{ $ressource->nom }}
                                            <span class="text-xs text-gray-500">· {{ $ressource->type_fichier }}</span>
                                        </button>
                                        <a href="{{ route('stagiaire.ressources.download', $ressource) }}" class="text-xs text-gray-500 hover:text-edl-bleu">↓</a>
                                    @endif
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
                                    <p class="text-xs text-gray-500">{{ $referentiel->module }} — {{ implode('/', $referentiel->niveaux ?: []) ?: 'tous niveaux' }}</p>
                                    @foreach ($referentiel->ressources as $ressource)
                                        @if ($op)
                                            <a href="{{ route('stagiaire.ressources.apercu', $ressource) }}"
                                               class="mt-0.5 block text-left text-xs text-edl-bleu hover:underline">
                                                ↳ {{ $ressource->nom }}
                                            </a>
                                        @else
                                            <button type="button"
                                                    @click="apercu='{{ route('stagiaire.ressources.download', $ressource) }}?apercu=1'; titre='{{ addslashes($ressource->nom) }}'; type='{{ $ressource->type_fichier }}'"
                                                    class="mt-0.5 block text-left text-xs text-edl-bleu hover:underline">
                                                ↳ {{ $ressource->nom }}
                                            </button>
                                        @endif
                                    @endforeach
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.card>
            </div>

            {{-- Volet de visualisation (hors OP) --}}
            @unless ($op)
            <div class="lg:sticky lg:top-6 lg:h-[70vh]">
                <div class="flex h-full flex-col rounded-lg bg-white shadow-sm"
                     oncontextmenu="return false" onselectstart="return false">
                    <p class="border-b border-gray-100 px-4 py-2 text-sm font-medium text-gray-600"
                       x-text="titre || 'Aperçu'"></p>
                    <template x-if="apercu && (type === 'video' || type === 'audio')">
                        <div class="relative min-h-[300px] flex-1">
                            <video :src="apercu" controls controlsList="nodownload noremoteplayback" disablepictureinpicture
                                   oncontextmenu="return false" class="h-full w-full rounded-b-lg bg-black"></video>
                            <x-stagiaire.filigrane :nombre="12" couleur="text-white" />
                        </div>
                    </template>
                    <template x-if="apercu && type !== 'video' && type !== 'audio'">
                        <div class="relative min-h-[300px] flex-1">
                            <iframe :src="apercu + '#toolbar=0&navpanes=0'" class="h-full w-full rounded-b-lg" title="Aperçu du document"></iframe>
                            <x-stagiaire.filigrane :nombre="12" />
                        </div>
                    </template>
                    <template x-if="!apercu">
                        <div class="flex flex-1 items-center justify-center p-6 text-center text-sm text-gray-500">
                            Sélectionnez un document pour l'afficher ici.
                        </div>
                    </template>
                </div>
            </div>
            @endunless
        </div>
    </x-stagiaire.shell>
</x-app-layout>
