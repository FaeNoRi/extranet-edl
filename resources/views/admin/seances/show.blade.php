<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            Séance du {{ $seance->date->format('d/m/Y') }}
        </h2>
    </x-slot>

    <x-admin.shell active="sessions">
        @include('seances._show', ['prefix' => 'admin'])
    </x-admin.shell>
</x-app-layout>
