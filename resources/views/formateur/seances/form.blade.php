@php $edition = $seance->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            Fiche pédagogique — {{ $session->nom }}
        </h2>
    </x-slot>

    <x-formateur.shell active="sessions">
        @include('seances._form', ['prefix' => 'formateur'])
    </x-formateur.shell>
</x-app-layout>
