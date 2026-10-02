@php $r = $registre; @endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 9px; color: #2b2521; margin: 0; }
        h1 { font-size: 15px; color: #156C93; margin: 0 0 2px; }
        .sous-titre { color: #58595C; font-size: 9px; margin-bottom: 10px; }
        h2 { font-size: 11px; color: #A52280; border-bottom: 1px solid #E31E73; padding-bottom: 2px; margin: 12px 0 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; page-break-inside: avoid; }
        td { border: 1px solid #e5ddd2; padding: 3px 5px; vertical-align: top; }
        td.k { background: #f6f2ec; color: #58595C; width: 16%; }
        ul { margin: 0; padding-left: 12px; }
        .projet { border: 1px solid #d9a600; background: #fff6d6; padding: 5px 7px; margin-bottom: 8px; }
        .attention { color: #A52280; }
        .pied { position: fixed; bottom: -14px; left: 0; right: 0; font-size: 7px; color: #8a8178; text-align: center; }
    </style>
</head>
<body>
    <div class="pied">Registre des activités de traitement — {{ $r['responsable']['nom'] }} — généré le {{ now()->format('d/m/Y') }}</div>

    <h1>Registre des activités de traitement</h1>
    <div class="sous-titre">Article 30 du RGPD — extranet EDL+</div>

    @if ($r['valide_le'])
        <div class="sous-titre">Validé par le responsable de traitement le {{ \Illuminate\Support\Carbon::parse($r['valide_le'])->format('d/m/Y') }}.</div>
    @else
        <div class="projet"><strong>Projet à valider</strong> par le responsable de traitement : bases légales et durées de conservation proposées à partir du fonctionnement de l'application.</div>
    @endif

    <h2>Responsable de traitement et cadre général</h2>
    <table>
        <tr><td class="k">Responsable</td><td>{{ $r['responsable']['nom'] }} — {{ $r['responsable']['forme'] }}, {{ $r['responsable']['adresse'] }}, SIRET {{ $r['responsable']['siret'] }}, {{ $r['responsable']['email'] }}</td></tr>
        <tr><td class="k">Délégué / référent RGPD</td><td>{{ $r['responsable']['dpo'] ?: 'Non renseigné' }}</td></tr>
        <tr><td class="k">Hébergeur (sous-traitant)</td><td>{{ $r['hebergeur'] }}</td></tr>
        <tr><td class="k">Transferts hors UE</td><td>{{ $r['transferts'] }}</td></tr>
        <tr><td class="k">Autres destinataires techniques</td><td><ul>@foreach ($r['destinataires_techniques'] as $d)<li>{{ $d }}</li>@endforeach</ul></td></tr>
        <tr><td class="k">Mesures de sécurité</td><td><ul>@foreach ($r['securite'] as $m)<li>{{ $m }}</li>@endforeach</ul></td></tr>
        <tr><td class="k attention">Points d'attention</td><td><ul>@foreach ($r['points_generaux'] as $p)<li>{{ $p }}</li>@endforeach</ul></td></tr>
    </table>

    @foreach ($r['traitements'] as $i => $t)
        <h2>{{ $i + 1 }}. {{ $t['nom'] }}</h2>
        <table>
            <tr><td class="k">Finalité</td><td>{{ $t['finalite'] }}</td></tr>
            <tr><td class="k">Base légale</td><td>{{ $t['base_legale'] }}</td></tr>
            <tr><td class="k">Personnes concernées</td><td>{{ $t['personnes'] }}</td></tr>
            <tr><td class="k">Catégories de données</td><td>{{ $t['donnees'] }}</td></tr>
            <tr><td class="k">Origine</td><td>{{ $t['origine'] }}</td></tr>
            <tr><td class="k">Destinataires</td><td>{{ $t['destinataires'] }}</td></tr>
            <tr><td class="k">Conservation</td><td><ul>@foreach ($t['conservation'] as $c)<li>{{ $c }}</li>@endforeach</ul></td></tr>
            @if ($t['points_attention'])
                <tr><td class="k attention">Points d'attention</td><td><ul>@foreach ($t['points_attention'] as $p)<li>{{ $p }}</li>@endforeach</ul></td></tr>
            @endif
        </table>
    @endforeach
</body>
</html>
