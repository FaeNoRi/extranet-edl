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
        <div class="flex min-h-screen flex-col bg-edl-fond text-edl-marron">
            <x-lisere-marque />
            @include('layouts.navigation')

            @isset($header)
                <header @class([
                    'relative overflow-hidden',
                    'bg-edl-bleu text-white' => $espace() === 'admin',
                    'bg-edl-orange text-gray-900' => $espace() === 'formateur',
                    'bg-edl-rose text-white' => $espace() === 'stagiaire',
                ])>
                    {{-- Globe du logo en filigrane --}}
                    <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true" focusable="false"
                         @class([
                             'pointer-events-none absolute -top-16 right-6 hidden h-64 w-64 sm:block lg:right-16',
                             'opacity-20' => $espace() !== 'formateur',
                             'text-white opacity-40' => $espace() === 'formateur',
                         ])>
                        <circle cx="50" cy="50" r="44"/><ellipse cx="50" cy="50" rx="20" ry="44"/><path d="M6 50h88M14 28h72M14 72h72"/>
                    </svg>
                    <div class="relative mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:px-8">
                        @isset($sousTitre)
                            <p class="mb-1 text-base">{{ $sousTitre }}</p>
                        @endisset
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
