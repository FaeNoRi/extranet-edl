@php $edition = $referentiel->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-edl-marron">
            {{ $edition ? 'Modifier une entrée du référentiel' : 'Nouvelle entrée du référentiel' }}
        </h1>
    </x-slot>

    <x-admin.shell active="referentiel">
        <x-admin.card>
            <form method="POST"
                  action="{{ $edition ? route('admin.referentiel.update', $referentiel) : route('admin.referentiel.store') }}"
                  class="space-y-5">
                @csrf
                @if ($edition) @method('PUT') @endif

                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <x-input-label for="module" :value="__('Module')" />
                        <select id="module" name="module" required
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            @foreach ($modules as $m)
                                <option value="{{ $m }}" @selected(old('module', $referentiel->module) === $m)>{{ $m }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('module')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="langue" :value="__('Langue')" />
                        <select id="langue" name="langue" aria-describedby="aide-langue"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            <option value="">Toutes langues</option>
                            @foreach ($langues as $l)
                                <option value="{{ $l }}" @selected(old('langue', $referentiel->langue) === $l)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <p id="aide-langue" class="mt-1 text-xs text-gray-500">Proposée aux formateurs des sessions de cette langue.</p>
                        <x-input-error :messages="$errors->get('langue')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="code" :value="__('Code')" />
                        <x-text-input id="code" name="code" class="mt-1 block w-full font-mono"
                                      :value="old('code', $referentiel->code)" required />
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="contenu" :value="__('Contenu')" />
                    <textarea id="contenu" name="contenu" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('contenu', $referentiel->contenu) }}</textarea>
                    <x-input-error :messages="$errors->get('contenu')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="badge" :value="__('Badge (facultatif)')" />
                    <x-text-input id="badge" name="badge" class="mt-1 block w-full" aria-describedby="aide-badge"
                                  :value="old('badge', $referentiel->badge)" maxlength="255" />
                    <p id="aide-badge" class="mt-1 text-xs text-gray-500">Texte libre, visible de l'administration et des formateurs.</p>
                    <x-input-error :messages="$errors->get('badge')" class="mt-1" />
                </div>

                <fieldset>
                    <legend class="text-sm font-medium text-gray-700">Niveaux</legend>
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach ($niveaux as $n)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="niveaux[]" value="{{ $n }}"
                                       @checked(collect(old('niveaux', $referentiel->niveaux))->contains($n))
                                       class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                {{ \App\Support\NiveauxReferentiel::libelleComplet($n) }}
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('niveaux')" class="mt-1" />
                </fieldset>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $edition ? 'Enregistrer' : 'Créer l\'entrée' }}</x-primary-button>
                    <a href="{{ route('admin.referentiel.index') }}" class="text-sm text-gray-500 hover:underline">Annuler</a>
                </div>
            </form>
        </x-admin.card>

        @if ($edition)
            <x-admin.card titre="Documents communs du code {{ $referentiel->code }}">
                <p class="mb-4 text-sm text-gray-600">
                    Ces documents apparaissent automatiquement dans chaque séance qui coche ce code, pour toutes les
                    sessions : dans le dossier de séance du formateur et dans les ressources du stagiaire
                    (consultation à l'écran uniquement pour les stagiaires OP).
                </p>

                @if ($referentiel->ressources->isEmpty())
                    <p class="mb-4 text-sm text-gray-500">Aucun document pour l'instant.</p>
                @else
                    <ul class="mb-4 divide-y divide-gray-100 text-sm">
                        @foreach ($referentiel->ressources as $ressource)
                            <li class="flex items-center justify-between gap-3 py-2">
                                <a href="{{ route('admin.ressources.download', $ressource) }}" class="text-edl-bleu hover:underline">
                                    {{ $ressource->nom }}
                                    <span class="text-xs text-gray-500">({{ $ressource->nom_fichier_original }})</span>
                                </a>
                                <form method="POST" action="{{ route('admin.referentiel.documents.destroy', [$referentiel, $ressource]) }}"
                                      x-data @submit="if (! confirm('Retirer ce document du code {{ $referentiel->code }} ?')) $event.preventDefault()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-edl-rose hover:underline">Retirer</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <form method="POST" action="{{ route('admin.referentiel.documents.store', $referentiel) }}"
                      enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="min-w-0 flex-1">
                        <x-input-label for="documents" :value="__('Ajouter des documents')" />
                        <input id="documents" type="file" name="documents[]" multiple required
                               class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-edl-bleu file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                        <x-input-error :messages="$errors->get('documents')" class="mt-1" />
                        <x-input-error :messages="$errors->get('documents.0')" class="mt-1" />
                    </div>
                    <x-primary-button>Ajouter</x-primary-button>
                </form>
            </x-admin.card>
        @endif
    </x-admin.shell>
</x-app-layout>
