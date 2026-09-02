<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            {{ __('Administration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white p-6 shadow sm:rounded-lg">
                <p class="text-gray-900">
                    Bonjour {{ auth()->user()->prenom }}, bienvenue sur l'espace d'administration.
                </p>
                <p class="mt-2 text-sm text-gray-500">
                    Gestion des comptes, import GESCOF, sessions et référentiel — à venir (phase&nbsp;2).
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
