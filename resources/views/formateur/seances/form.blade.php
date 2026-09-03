@php $edition = $seance->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            Fiche pédagogique — {{ $session->nom }}
        </h2>
    </x-slot>

    <x-formateur.shell active="sessions">
        <x-admin.card>
            <form method="POST"
                  action="{{ $edition ? route('formateur.seances.update', $seance) : route('formateur.seances.store') }}"
                  enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if ($edition) @method('PUT') @endif
                <input type="hidden" name="session_formation_id" value="{{ $session->id }}">

                {{-- Champs récupérés automatiquement --}}
                <div class="rounded-md bg-gray-50 p-4 text-sm text-gray-600">
                    <p><span class="text-gray-400">Stage :</span> {{ $session->nom }} — session {{ $session->num_GESCOF }}</p>
                    <p><span class="text-gray-400">Formateur :</span> {{ auth()->user()->nom_complet }}</p>
                    <p><span class="text-gray-400">Langue :</span> {{ $session->langue }}</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="date" :value="__('Date de la séance')" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                                      :value="old('date', optional($seance->date)->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('date')" class="mt-1" />
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

                <fieldset>
                    <legend class="text-sm font-medium text-gray-700">Modules du référentiel vus pendant la séance</legend>
                    <div class="mt-2 grid gap-4 sm:grid-cols-2">
                        @php $refSel = old('referentiels', $edition ? $seance->referentiels->pluck('id')->all() : []); @endphp
                        @foreach ($modules as $module => $entrees)
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ $module }}</p>
                                @foreach ($entrees as $entree)
                                    <label class="flex items-start gap-2 py-0.5 text-sm">
                                        <input type="checkbox" name="referentiels[]" value="{{ $entree->id }}" @checked(in_array($entree->id, $refSel))
                                               class="mt-0.5 rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                        <span><span class="font-mono text-xs">{{ $entree->code }}</span> {{ $entree->contenu }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label :value="__('Documents mis à disposition du stagiaire')" />
                        <input type="file" name="fichiers_transmis[]" multiple
                               class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-edl-vert-fonce file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                        <x-input-error :messages="$errors->get('fichiers_transmis.0')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label :value="__('Documents de travail (non transmis)')" />
                        <input type="file" name="fichiers_internes[]" multiple
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
                    <a href="{{ $edition ? route('formateur.seances.show', $seance) : route('formateur.sessions.show', $session) }}"
                       class="text-sm text-gray-500 hover:underline">Annuler</a>
                </div>
            </form>
        </x-admin.card>
    </x-formateur.shell>
</x-app-layout>
