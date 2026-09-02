<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Choisissez le mot de passe qui vous permettra de vous connecter à votre espace.') }}
    </div>

    <form method="POST" action="{{ route('password.store', ['token' => $token]) }}">
        @csrf

        <!-- Mot de passe -->
        <div>
            <x-input-label for="password" :value="__('Mot de passe')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" autofocus />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmation -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Valider le mot de passe') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
