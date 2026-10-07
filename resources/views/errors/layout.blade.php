<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titre') — {{ config('app.name', 'Extranet EDL+') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f3f4f6; color: #111827; font-family: Abel, system-ui, sans-serif; font-size: 18px; }
        main { max-width: 32rem; margin: 1rem; padding: 2rem; background: #fff; border-radius: .5rem; box-shadow: 0 1px 4px rgba(0, 0, 0, .15); }
        .code { color: #156c93; font-size: .95rem; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: .25rem 0 1rem; font-size: 1.6rem; }
        a { color: #156c93; }
        a:focus-visible { outline: 2px solid #156c93; outline-offset: 2px; }
    </style>
</head>
<body>
    <main>
        <p class="code">Erreur @yield('code')</p>
        <h1>@yield('titre')</h1>
        <p>@yield('message')</p>
        @hasSection('aide')
            <p>@yield('aide')</p>
        @endif
        <p><a href="/">Retour à l'accueil</a></p>
    </main>
</body>
</html>
