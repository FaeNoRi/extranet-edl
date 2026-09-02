@php $edition = $formateur->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            {{ $edition ? 'Modifier un formateur' : 'Nouveau formateur' }}
        </h2>
    </x-slot>

    <x-admin.shell active="formateurs">
        <x-admin.card>
            <form method="POST"
                  action="{{ $edition ? route('admin.formateurs.update', $formateur) : route('admin.formateurs.store') }}"
                  enctype="multipart/form-data" class="space-y-5">
                @csrf
                @if ($edition) @method('PUT') @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="prenom" :value="__('Prénom')" />
                        <x-text-input id="prenom" name="prenom" class="mt-1 block w-full"
                                      :value="old('prenom', $formateur->prenom)" required />
                        <x-input-error :messages="$errors->get('prenom')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="nom" :value="__('Nom')" />
                        <x-text-input id="nom" name="nom" class="mt-1 block w-full"
                                      :value="old('nom', $formateur->nom)" required />
                        <x-input-error :messages="$errors->get('nom')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="login" :value="__('Identifiant')" />
                        <x-text-input id="login" name="login" class="mt-1 block w-full"
                                      :value="old('login', $formateur->login)" required />
                        <x-input-error :messages="$errors->get('login')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Adresse e-mail')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                      :value="old('email', $formateur->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                </div>

                <fieldset>
                    <legend class="text-sm font-medium text-gray-700">Interventions</legend>
                    <div class="mt-2 flex gap-6">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="formateur_fpc" value="1" @checked(old('formateur_fpc', $formateur->formateur_fpc))
                                   class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu"> FPC
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="formateur_op" value="1" @checked(old('formateur_op', $formateur->formateur_op))
                                   class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu"> OP
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('formateur_op')" class="mt-1" />
                </fieldset>

                <div>
                    <x-input-label for="presentation" :value="__('Phrase de présentation (visible par les stagiaires)')" />
                    <textarea id="presentation" name="presentation" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-edl-bleu focus:ring-edl-bleu">{{ old('presentation', $formateur->presentation) }}</textarea>
                    <x-input-error :messages="$errors->get('presentation')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="photo" :value="__('Photo')" />
                    @if ($formateur->photo_path)
                        <img src="{{ Storage::url($formateur->photo_path) }}" alt="" class="my-2 h-20 w-20 rounded-full object-cover">
                    @endif
                    <input id="photo" name="photo" type="file" accept="image/*" class="mt-1 block text-sm text-gray-700">
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>

                @unless ($edition)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="envoyer_acces" value="1" checked
                               class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                        Envoyer le lien de création de mot de passe
                    </label>
                @endunless

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $edition ? 'Enregistrer' : 'Créer le formateur' }}</x-primary-button>
                    <a href="{{ route('admin.formateurs.index') }}" class="text-sm text-gray-500 hover:underline">Annuler</a>
                </div>
            </form>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
