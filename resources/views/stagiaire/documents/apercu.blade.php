<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">{{ $document->nom }}</h2>
    </x-slot>

    <x-stagiaire.shell active="dashboard" large>
        <a href="{{ route('stagiaire.dashboard') }}" class="text-sm text-edl-bleu hover:underline">← Mon espace</a>

        <x-stagiaire.apercu-durci :url="$url" :type="$type" :titre="$document->nom" />
    </x-stagiaire.shell>
</x-app-layout>
