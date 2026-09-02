<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            {{ __('Mon espace') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white p-6 shadow sm:rounded-lg">
                <p class="text-sm text-gray-500">Bienvenue sur votre espace</p>
                <p class="mt-1 text-lg font-medium text-edl-marron">{{ auth()->user()->nom_complet }}</p>
                <p class="mt-4 text-sm text-gray-500">
                    Documents, planning et ressources pédagogiques — à venir (phase&nbsp;4).
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
