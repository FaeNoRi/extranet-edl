{{-- Contenu partagé entre formateur.seances.form et admin.seances.form. --}}
@php
    $prefix ??= 'formateur';
    $edition = $seance->exists;
@endphp

<x-admin.card>
    <form method="POST"
          action="{{ $edition ? route("{$prefix}.seances.update", $seance) : route('formateur.seances.store') }}"
          enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if ($edition) @method('PUT') @endif
        <input type="hidden" name="session_formation_id" value="{{ $session->id }}">

        {{-- Champs récupérés automatiquement --}}
        <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">
            <p><span class="text-gray-500">Stage :</span> {{ $session->nom }} — session {{ $session->num_GESCOF }}</p>
            <p><span class="text-gray-500">Formateur :</span> {{ ($edition ? $seance->formateur?->nom_complet : null) ?? auth()->user()->nom_complet }}</p>
            <p><span class="text-gray-500">Langue :</span> {{ $session->langue }}</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="date" :value="__('Date de la séance')" />
                <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                              :value="old('date', optional($seance->date)->format('Y-m-d'))" required />
                <x-input-error :messages="$errors->get('date')" class="mt-1" />
                {{-- Pré-cochée pour une nouvelle fiche d'une session en distanciel ; modifiable séance par séance. --}}
                <label class="mt-3 flex items-center gap-2 text-sm">
                    <input type="checkbox" name="distanciel" value="1"
                           @checked(session()->hasOldInput() ? old('distanciel') : ($edition ? $seance->distanciel : $session->distanciel))
                           class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                    Séance en distanciel
                </label>
            </div>

            @if ($session->isFpc())
                <div>
                    <x-input-label for="user_id" :value="__('Stagiaire')" />
                    <select id="user_id" name="user_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                        <option value="">— Choisir —</option>
                        @foreach ($stagiaires as $stagiaire)
                            <option value="{{ $stagiaire->id }}" @selected(old('user_id', $seance->user_id) == $stagiaire->id)>{{ $stagiaire->nom_complet }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-1" />
                </div>
            @endif
        </div>

        <fieldset>
            <legend class="text-sm font-medium text-gray-700">Objectifs</legend>
            <div class="mt-2 space-y-1.5">
                @php $objSel = old('objectifs', $seance->objectifs ?? []); @endphp
                @foreach ($objectifsProposes as $objectif)
                    <label class="flex items-start gap-2 text-sm">
                        <input type="checkbox" name="objectifs[]" value="{{ $objectif }}" @checked(in_array($objectif, $objSel))
                               class="mt-0.5 rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                        <span>{{ $objectif }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div>
            <x-input-label for="contenu" :value="__('Contenu de la séance (vocabulaire, grammaire, prononciation, révision…)')" />
            <textarea id="contenu" name="contenu" rows="4" required
                      class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('contenu', $seance->contenu) }}</textarea>
            <x-input-error :messages="$errors->get('contenu')" class="mt-1" />
        </div>

        <fieldset>
            <legend class="text-sm font-medium text-gray-700">Types d'outils et de supports utilisés</legend>
            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
                @php $outSel = old('outils', $seance->outils ?? []); @endphp
                @foreach ($outils as $outil)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="outils[]" value="{{ $outil }}" @checked(in_array($outil, $outSel))
                               class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                        {{ $outil }}
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div>
            <x-input-label for="sources" :value="__('Sources (livres, magazines, articles, liens de sites, vidéos…)')" />
            <textarea id="sources" name="sources" rows="2" required
                      class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('sources', $seance->sources) }}</textarea>
            <x-input-error :messages="$errors->get('sources')" class="mt-1" />
        </div>

        {{-- Référentiel en accordéon : un module par section (<details> natif, clavier et lecteur
             d'écran compris). Les modules déjà cochés s'ouvrent d'office ; l'en-tête compte les cases. --}}
        <fieldset>
            <legend class="text-sm font-medium text-gray-700">Modules du référentiel vus pendant la séance</legend>
            @php $refSel = array_map('intval', old('referentiels', $edition ? $seance->referentiels->pluck('id')->all() : [])); @endphp
            <div class="mt-2 divide-y divide-gray-200 rounded-md border border-gray-200">
                @foreach ($modules as $module => $entrees)
                    @php $coches = $entrees->whereIn('id', $refSel)->count(); @endphp
                    <details class="group" @if ($coches > 0) open @endif x-data="{ n: {{ $coches }} }">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-2.5 text-sm font-semibold text-edl-marron hover:bg-gray-50">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-gray-500 transition-transform group-open:rotate-90" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.2 14.8a.75.75 0 0 1 0-1.06L10.94 10 7.2 6.26a.75.75 0 1 1 1.06-1.06l4.27 4.27a.75.75 0 0 1 0 1.06l-4.27 4.27a.75.75 0 0 1-1.06 0Z" clip-rule="evenodd"/></svg>
                                {{ $module }}
                            </span>
                            <span class="text-xs font-normal text-gray-600">
                                <span x-text="n">{{ $coches }}</span> cochée(s) sur {{ $entrees->count() }}
                            </span>
                        </summary>
                        <div class="grid gap-x-6 gap-y-1 px-4 pb-3 pt-1 sm:grid-cols-2">
                            @foreach ($entrees as $entree)
                                <div class="py-0.5 text-sm">
                                    <label class="flex items-start gap-2">
                                        <input type="checkbox" name="referentiels[]" value="{{ $entree->id }}" @checked(in_array($entree->id, $refSel))
                                               @change="n += $event.target.checked ? 1 : -1"
                                               class="mt-0.5 rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                        <span>
                                            <span class="font-mono text-xs">{{ $entree->code }}</span> {{ $entree->contenu }}
                                            <span class="text-xs text-gray-600">({{ $entree->niveauxAffiches() }})</span>
                                            @foreach ($entree->badges ?? [] as $badge)
                                                <span class="ml-1 inline-block rounded-full bg-edl-jaune/20 px-2 py-0.5 text-xs font-medium text-gray-800">{{ $badge }}</span>
                                            @endforeach
                                        </span>
                                    </label>
                                    {{-- Documents communs du code : consultables avant de cocher, ajoutés au dossier de séance. --}}
                                    @if ($entree->ressources->isNotEmpty())
                                        <ul class="ml-6 mt-0.5 space-y-0.5 text-xs">
                                            @foreach ($entree->ressources as $document)
                                                <li>
                                                    <a href="{{ route("{$prefix}.ressources.download", $document) }}" class="text-edl-bleu hover:underline">
                                                        ↳ {{ $document->nom }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        </fieldset>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="fichiers_transmis" :value="__('Documents mis à disposition du stagiaire')" />
                <input id="fichiers_transmis" type="file" name="fichiers_transmis[]" multiple
                       class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-edl-vert-fonce file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                <x-input-error :messages="$errors->get('fichiers_transmis.0')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="fichiers_internes" :value="__('Documents de travail (non transmis)')" />
                <input id="fichiers_internes" type="file" name="fichiers_internes[]" multiple
                       class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-gray-500 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                <x-input-error :messages="$errors->get('fichiers_internes.0')" class="mt-1" />
            </div>
        </div>

        @if ($ressourcesSession->isNotEmpty())
            <fieldset>
                <legend class="text-sm font-medium text-gray-700">Ou réutiliser une ressource de la session</legend>
                <div class="mt-2 grid gap-1 sm:grid-cols-2">
                    @php $resSel = old('ressources', $edition ? $seance->ressources->pluck('id')->all() : []); @endphp
                    @foreach ($ressourcesSession as $ressource)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="ressources[]" value="{{ $ressource->id }}" @checked(in_array($ressource->id, $resSel))
                                   class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                            {{ $ressource->nom }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif

        <div>
            <x-input-label for="analyse_seance" :value="__('Analyse de la séance (observation, participation, difficultés, améliorations…)')" />
            <textarea id="analyse_seance" name="analyse_seance" rows="4" required
                      class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('analyse_seance', $seance->analyse_seance) }}</textarea>
            <x-input-error :messages="$errors->get('analyse_seance')" class="mt-1" />
        </div>

        <div class="flex items-center gap-3">
            <x-primary-button>Enregistrer</x-primary-button>
            <a href="{{ $edition ? route("{$prefix}.seances.show", $seance) : route('formateur.sessions.show', $session) }}"
               class="text-sm text-gray-500 hover:underline">Annuler</a>
        </div>
    </form>
</x-admin.card>
