@php /** @var \App\Models\Seance $seance */ $s = $seance; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 11px; color: #2b2521; margin: 0; }
        h1 { font-size: 16px; color: #156C93; margin: 0 0 2px; }
        .sous-titre { color: #58595C; font-size: 10px; margin-bottom: 14px; }
        h2 { font-size: 12px; color: #A52280; border-bottom: 1px solid #E31E73; padding-bottom: 2px; margin: 14px 0 6px; }
        table.infos { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.infos td { padding: 2px 4px; vertical-align: top; }
        table.infos td.k { color: #58595C; width: 130px; }
        ul { margin: 4px 0; padding-left: 16px; }
        .bloc { border: 1px solid #e5ddd2; padding: 6px 8px; margin-top: 4px; white-space: pre-wrap; }
        .pied { position: fixed; bottom: -18px; left: 0; right: 0; font-size: 8px; color: #8a8178; text-align: center; }
    </style>
</head>
<body>
    <div class="pied">Fiche pédagogique — École des Langues Grand Calais — document interne (formateur / administration)</div>

    <h1>Fiche pédagogique</h1>
    <div class="sous-titre">
        {{ $s->sessionFormation->nom }} — session {{ $s->sessionFormation->num_GESCOF }}
        @if ($s->stagiaire) · {{ $s->stagiaire->nom_complet }} @endif
    </div>

    <table class="infos">
        <tr><td class="k">Date de la séance</td><td>{{ $s->date->translatedFormat('l d F Y') }}</td></tr>
        <tr><td class="k">Formateur</td><td>{{ $s->formateur?->nom_complet ?? '—' }}</td></tr>
        <tr><td class="k">Langue</td><td>{{ $s->langue ?? $s->sessionFormation->langue }}</td></tr>
        <tr><td class="k">Client</td><td>{{ $s->sessionFormation->client?->nom ?? '—' }}</td></tr>
    </table>

    <h2>Objectifs</h2>
    @if ($s->objectifs)
        <ul>@foreach ($s->objectifs as $o)<li>{{ $o }}</li>@endforeach</ul>
    @else
        <p>—</p>
    @endif

    <h2>Contenu de la séance</h2>
    <div class="bloc">{{ $s->contenu ?: '—' }}</div>

    <h2>Outils et supports</h2>
    <p>{{ $s->outils ? implode(', ', $s->outils) : '—' }}</p>

    <h2>Sources</h2>
    <div class="bloc">{{ $s->sources ?: '—' }}</div>

    <h2>Modules du référentiel vus</h2>
    @if ($s->referentiels->isNotEmpty())
        <ul>@foreach ($s->referentiels as $r)<li>{{ $r->code }} — {{ $r->contenu }} ({{ $r->module }})</li>@endforeach</ul>
    @else
        <p>—</p>
    @endif

    <h2>Documents utilisés</h2>
    <ul>
        @forelse ($s->ressources as $ressource)
            <li>{{ $ressource->nom }} — {{ $ressource->pivot->transmis ? 'transmis au stagiaire' : 'non transmis' }}</li>
        @empty
            <li>—</li>
        @endforelse
    </ul>

    <h2>Analyse de la séance</h2>
    <div class="bloc">{{ $s->analyse_seance ?: '—' }}</div>
</body>
</html>
