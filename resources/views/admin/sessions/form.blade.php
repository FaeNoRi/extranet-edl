@php $edition = $session->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            {{ $edition ? 'Modifier la session' : 'Nouvelle session' }}
        </h2>
    </x-slot>

    <x-admin.shell active="sessions">
        <x-admin.card>
            <form method="POST" x-data="{ produit: '{{ old('code_produit', $session->code_produit->value ?? 'OP') }}' }"
                  action="{{ $edition ? route('admin.sessions.update', $session) : route('admin.sessions.store') }}"
                  class="space-y-5">
                @csrf
                @if ($edition) @method('PUT') @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="num_GESCOF" :value="__('N° de session GESCOF')" />
                        <x-text-input id="num_GESCOF" name="num_GESCOF" class="mt-1 block w-full"
                                      :value="old('num_GESCOF', $session->num_GESCOF)" required />
                        <x-input-error :messages="$errors->get('num_GESCOF')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="code_stage" :value="__('Code stage (facultatif)')" />
                        <x-text-input id="code_stage" name="code_stage" class="mt-1 block w-full"
                                      :value="old('code_stage', $session->code_stage)" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="nom" :value="__('Libellé du stage')" />
                        <x-text-input id="nom" name="nom" class="mt-1 block w-full"
                                      :value="old('nom', $session->nom)" required />
                        <x-input-error :messages="$errors->get('nom')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="code_produit" :value="__('Produit')" />
                        <select id="code_produit" name="code_produit" x-model="produit"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            @foreach ($produits as $p)
                                <option value="{{ $p->value }}" @selected(old('code_produit', $session->code_produit->value ?? '') === $p->value)>{{ $p->value }} — {{ $p->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="langue" :value="__('Langue')" />
                        <x-text-input id="langue" name="langue" class="mt-1 block w-full"
                                      :value="old('langue', $session->langue ?: 'Anglais')" required />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="client_id" :value="__('Client')" />
                        <select id="client_id" name="client_id"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            <option value="">— Aucun —</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id', $session->client_id) == $client->id)>{{ $client->nom }}</option>
                            @endforeach
                        </select>
                        <x-text-input name="nouveau_client" class="mt-2 block w-full" placeholder="… ou nouveau client"
                                      :value="old('nouveau_client')" />
                    </div>
                    <div>
                        <x-input-label for="formateur_id" :value="__('Formateur référent')" />
                        <select id="formateur_id" name="formateur_id"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            <option value="">— À affecter —</option>
                            @foreach ($formateurs as $f)
                                <option value="{{ $f->id }}" @selected(old('formateur_id', $session->formateur_id) == $f->id)>{{ $f->nom_complet }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <x-input-label :value="__('Équipe pédagogique (co-animation)')" />
                    <div class="mt-1 grid gap-1 sm:grid-cols-3">
                        @php $equipe = old('formateurs', $edition ? $session->formateurs->pluck('id')->all() : []); @endphp
                        @foreach ($formateurs as $f)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="formateurs[]" value="{{ $f->id }}" @checked(in_array($f->id, $equipe))
                                       class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                                {{ $f->nom_complet }}
                            </label>
                        @endforeach
                    </div>
                    @if ($edition && $session->intervenants_import)
                        <p class="mt-2 text-xs text-gray-400">Import GESCOF : « {{ $session->intervenants_import }} »</p>
                    @endif
                </div>

                <div x-show="produit === 'OP'" class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="rythme_op" :value="__('Rythme (OP)')" />
                        <select id="rythme_op" name="rythme_op"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">
                            <option value="">—</option>
                            <option value="trimestre" @selected(old('rythme_op', $session->rythme_op) === 'trimestre')>Au trimestre</option>
                            <option value="annee" @selected(old('rythme_op', $session->rythme_op) === 'annee')>À l'année</option>
                        </select>
                        <x-input-error :messages="$errors->get('rythme_op')" class="mt-1" />
                    </div>
                </div>

                <div x-show="produit === 'FPC'" class="space-y-5">
                    <div>
                        <x-input-label for="objectifs" :value="__('Objectifs personnalisés (FPC)')" />
                        <textarea id="objectifs" name="objectifs" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('objectifs', $session->objectifs) }}</textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="distanciel" value="1" @checked(old('distanciel', $session->distanciel))
                               class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                        Formation intégralement à distance (émargements, éval. des acquis, questionnaire à chaud en ligne)
                    </label>
                    <div>
                        <x-input-label for="lien_teams" :value="__('Lien Teams')" />
                        <x-text-input id="lien_teams" name="lien_teams" type="url" class="mt-1 block w-full"
                                      :value="old('lien_teams', $session->lien_teams)" />
                        <x-input-error :messages="$errors->get('lien_teams')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="dates_planning" :value="__('Dates de planning (collage depuis GESCOF, brut)')" />
                    <textarea id="dates_planning" name="dates_planning" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('dates_planning', $session->dates_planning) }}</textarea>
                    <p class="mt-1 text-xs text-gray-400">Le planning jour par jour se gère depuis la fiche de la session.</p>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $edition ? 'Enregistrer' : 'Créer la session' }}</x-primary-button>
                    <a href="{{ $edition ? route('admin.sessions.show', $session) : route('admin.sessions.index') }}"
                       class="text-sm text-gray-500 hover:underline">Annuler</a>
                </div>
            </form>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
