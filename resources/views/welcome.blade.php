<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Extranet EDL+') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="flex min-h-screen flex-col bg-gray-100">
        <main class="flex flex-1 items-center justify-center px-4 py-16">
            <div class="w-full max-w-lg text-center">
                <p class="text-3xl font-semibold text-edl-bleu">
                    Extranet <span class="text-edl-rose">EDL+</span>
                </p>
                <p class="mt-3 text-gray-600">
                    Espace de suivi pédagogique de l'École des Langues Grand Calais —
                    stagiaires, formateurs et administration.
                </p>

                <div class="mt-8">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center rounded-md bg-edl-bleu px-5 py-3 text-sm font-semibold text-white transition hover:bg-edl-vert-fonce">
                            Accéder à mon espace
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center rounded-md bg-edl-bleu px-5 py-3 text-sm font-semibold text-white transition hover:bg-edl-vert-fonce">
                            Se connecter
                        </a>
                    @endauth
                </div>

                <p class="mt-6 text-xs text-gray-500">
                    L'accès se fait avec l'identifiant reçu par e-mail lors de la création du compte.
                </p>
            </div>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
