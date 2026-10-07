<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@isset($header){{ trim(preg_replace('/\s+/', ' ', strip_tags((string) $header))) }} — @endisset{{ config('app.name', 'Extranet EDL+') }}</title>
        <link rel="icon" type="image/png" href="{{ asset(config('edl.favicon')) }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <a href="#contenu-principal" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-white focus:px-4 focus:py-2 focus:font-semibold focus:text-edl-bleu focus:shadow-lg">Aller au contenu</a>
        <div class="flex min-h-screen flex-col bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main id="contenu-principal" tabindex="-1" class="flex-1">
                {{ $slot }}
            </main>

            <x-site-footer />
        </div>
    </body>
</html>
