@php $edition = $administrateur->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">
            {{ $edition ? 'Modifier un administrateur' : 'Nouvel administrateur' }}
        </h1>
    </x-slot>

    <x-admin.shell active="administrateurs">
        <x-admin.card>
            <form method="POST"
                  action="{{ $edition ? route('admin.administrateurs.update', $administrateur) : route('admin.administrateurs.store') }}"
                  class="space-y-5">
                @csrf
                @if ($edition) @method('PUT') @endif

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="prenom" :value="__('Prénom')" />
                        <x-text-input id="prenom" name="prenom" class="mt-1 block w-full"
                                      :value="old('prenom', $administrateur->prenom)" required />
                        <x-input-error :messages="$errors->get('prenom')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="nom" :value="__('Nom')" />
                        <x-text-input id="nom" name="nom" class="mt-1 block w-full"
                                      :value="old('nom', $administrateur->nom)" required />
                        <x-input-error :messages="$errors->get('nom')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="login" :value="__('Identifiant')" />
                        <x-text-input id="login" name="login" class="mt-1 block w-full"
                                      :value="old('login', $administrateur->login)" required />
                        <x-input-error :messages="$errors->get('login')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Adresse e-mail')" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                                      :value="old('email', $administrateur->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>
                </div>

                @unless ($edition)
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="envoyer_acces" value="1"
                               class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                        Envoyer le lien de création de mot de passe par e-mail
                    </label>
                    <p class="text-sm text-gray-500">
                        Sinon (ou tant que les e-mails sont coupés), utilisez « Lien d'accès » dans la liste et transmettez le lien vous-même.
                    </p>
                @endunless

                <div class="flex items-center gap-3">
                    <x-primary-button>{{ $edition ? 'Enregistrer' : 'Créer l\'administrateur' }}</x-primary-button>
                    <a href="{{ route('admin.administrateurs.index') }}" class="text-sm text-gray-500 hover:underline">Annuler</a>
                </div>
            </form>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
