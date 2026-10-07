<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold leading-tight text-edl-marron">{{ $ressource->nom }}</h1>
    </x-slot>

    <x-stagiaire.shell active="ressources" large>
        <a href="{{ url()->previous(route('stagiaire.ressources.index')) }}" class="text-sm text-edl-bleu hover:underline">← Retour</a>

        <x-stagiaire.apercu-durci :url="$url" :type="$type" :titre="$ressource->nom" />
    </x-stagiaire.shell>
</x-app-layout>
