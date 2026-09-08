<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Extranet EDL+') }}</title>
        <link rel="icon" type="image/png" href="{{ asset(config('edl.favicon')) }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 px-4 py-10">
            <a href="{{ route('accueil') }}" class="flex items-center gap-3">
                <img src="{{ asset(config('edl.logo')) }}" alt="" class="h-11 w-auto">
                <span class="text-2xl font-semibold text-edl-bleu">Extranet <span class="text-edl-rose">EDL+</span></span>
            </a>

            <div class="mt-6 w-full overflow-hidden bg-white px-6 py-6 shadow-md sm:max-w-md sm:rounded-lg">
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-gray-500">
                {{ config('edl.structure.nom') }}
            </p>
            <nav class="mt-2 flex flex-wrap justify-center gap-x-4 gap-y-1 text-xs text-gray-400">
                <a class="hover:text-edl-bleu" href="{{ route('legal.mentions') }}">Mentions légales</a>
                <a class="hover:text-edl-bleu" href="{{ route('legal.confidentialite') }}">Confidentialité</a>
                <a class="hover:text-edl-bleu" href="{{ route('legal.accessibilite') }}">Accessibilité</a>
            </nav>

            <x-logos-partenaires compact class="mt-6 max-w-md" />
        </div>
    </body>
</html>
