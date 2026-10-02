<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-edl-marron">
            Séance du {{ $seance->date->format('d/m/Y') }}
        </h1>
    </x-slot>

    <x-formateur.shell active="sessions">
        @include('seances._show', ['prefix' => 'formateur'])
    </x-formateur.shell>
</x-app-layout>
