<section>
    <header>
        <h2 class="text-lg font-medium text-edl-marron">
            {{ __('Informations personnelles') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Votre identifiant et votre adresse e-mail sont gérés par l'administration de l'EDL.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="prenom" :value="__('Prénom')" />
            <x-text-input id="prenom" name="prenom" type="text" class="mt-1 block w-full" :value="old('prenom', $user->prenom)" required autofocus autocomplete="given-name" />
            <x-input-error class="mt-2" :messages="$errors->get('prenom')" />
        </div>

        <div>
            <x-input-label for="nom" :value="__('Nom')" />
            <x-text-input id="nom" name="nom" type="text" class="mt-1 block w-full" :value="old('nom', $user->nom)" required autocomplete="family-name" />
            <x-input-error class="mt-2" :messages="$errors->get('nom')" />
        </div>

        <div>
            <x-input-label :value="__('Identifiant')" />
            <p class="mt-1 text-sm text-gray-900">{{ $user->login }}</p>
        </div>

        <div>
            <x-input-label :value="__('Adresse e-mail')" />
            <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Enregistré.') }}</p>
            @endif
        </div>
    </form>
</section>
