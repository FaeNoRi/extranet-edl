<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __("Saisissez votre identifiant : si un compte y correspond, vous recevrez par e-mail un lien pour définir un nouveau mot de passe.") }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="login" :value="__('Identifiant')" />
            <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Recevoir un lien par e-mail') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
