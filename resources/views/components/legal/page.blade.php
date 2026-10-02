@props(['titre'])

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $titre }} — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(config('edl.favicon')) }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-gray-800">
    <div class="mx-auto max-w-3xl px-4 py-12">
        <a href="{{ route('accueil') }}" class="text-lg font-semibold text-edl-bleu">Extranet <span class="text-edl-rose">EDL+</span></a>

        <h1 class="mt-8 text-3xl font-semibold text-edl-marron">{{ $titre }}</h1>

        <div class="prose-edl mt-6 space-y-4 text-sm leading-relaxed [&_h2]:mt-8 [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-edl-marron [&_ul]:list-disc [&_ul]:pl-5 [&_a]:text-edl-bleu [&_a]:underline">
            {{ $slot }}
        </div>

        <p class="mt-12 text-xs text-gray-400">
            Dernière mise à jour : {{ now()->translatedFormat('F Y') }}.
        </p>
    </div>
</body>
</html>
