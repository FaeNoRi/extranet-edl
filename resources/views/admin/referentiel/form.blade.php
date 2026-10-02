@php $edition = $referentiel->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            {{ $edition ? 'Modifier une entrée du référentiel' : 'Nouvelle entrée du référentiel' }}
        </h2>
    </x-slot>

    <x-admin.shell active="referentiel">
        <x-admin.card>
            <form method="POST"
                  action="{{ $edition ? route('admin.referentiel.update', $referentiel) : route('admin.referentiel.store') }}"
                  class="space-y-5">
                @csrf
                @if ($edition) @method('PUT') @endif

                <div class="grid gap-5 sm:grid-cols-2">
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

                <fieldset>
                    <legend class="text-sm font-medium text-gray-700">Niveaux (CECRL)</legend>
                    <div class="mt-2 flex flex-wrap gap-4">
                        @foreach ($niveaux as $n)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="niveaux[]" value="{{ $n }}"
                                       @checked(collect(old('niveaux', $referentiel->niveaux))->contains($n))
                                       class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu"> {{ $n }}
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
    </x-admin.shell>
</x-app-layout>
