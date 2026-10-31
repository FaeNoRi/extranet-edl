@php $edition = $seance->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">
            Fiche pédagogique — {{ $session->nom }}
        </h1>
    </x-slot>

    <x-formateur.shell active="sessions">
        @include('seances._form', ['prefix' => 'formateur'])
    </x-formateur.shell>
</x-app-layout>
