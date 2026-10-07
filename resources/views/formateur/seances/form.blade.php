@php $edition = $seance->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-edl-marron">
            Fiche pédagogique — {{ $session->nom }}
        </h1>
    </x-slot>

    <x-formateur.shell active="sessions">
        @include('seances._form', ['prefix' => 'formateur'])
    </x-formateur.shell>
</x-app-layout>
