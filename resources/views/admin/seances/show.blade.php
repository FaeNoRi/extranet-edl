<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">
            Séance du {{ $seance->date->format('d/m/Y') }}
        </h1>
    </x-slot>

    <x-admin.shell active="sessions">
        @include('seances._show', ['prefix' => 'admin'])
    </x-admin.shell>
</x-app-layout>
