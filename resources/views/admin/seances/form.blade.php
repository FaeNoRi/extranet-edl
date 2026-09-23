@php $edition = $seance->exists; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            Fiche pédagogique — {{ $session->nom }}
        </h2>
    </x-slot>

    <x-admin.shell active="sessions">
        @include('seances._form', ['prefix' => 'admin'])
    </x-admin.shell>
</x-app-layout>
