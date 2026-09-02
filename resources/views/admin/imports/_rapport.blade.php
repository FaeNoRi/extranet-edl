@php
    /** @var \App\Models\GescofImport $import */
    $anomalies = collect($import->anomalies ?? []);
    $parType = $anomalies->groupBy('type');
    $libelles = [
        'acces_refuse' => 'Accès plateforme ≠ Oui',
        'hors_perimetre' => 'Hors périmètre (stage, CLSH, immersion…)',
        'participant_absent' => 'Participant non renseigné',
        'session_absente' => 'Numéro de session manquant',
        'email_invalide' => 'E-mail manquant ou invalide',
        'formateur_non_reconnu' => 'Formateur non reconnu',
    ];
@endphp

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['Lignes lues', $import->lignes_lues],
        ['Lignes ignorées', $import->lignes_ignorees],
        ['Sessions créées', $import->sessions_creees],
        ['Sessions mises à jour', $import->sessions_maj],
        ['Comptes créés', $import->comptes_crees],
        ['Comptes réactivés', $import->comptes_reactives],
        ['Comptes disparus', $import->comptes_disparus],
        ['Anomalies', $anomalies->count()],
    ] as [$label, $valeur])
        <div class="rounded-md border border-gray-100 bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-semibold text-gray-800">{{ $valeur }}</p>
        </div>
    @endforeach
</div>

@if ($anomalies->isNotEmpty())
    <div class="mt-6 space-y-4">
        @foreach ($parType as $type => $liste)
            <div>
                <p class="text-sm font-semibold text-edl-marron">
                    {{ $libelles[$type] ?? $type }}
                    <span class="ml-1 rounded-full bg-edl-jaune/20 px-2 py-0.5 text-xs text-edl-marron">{{ $liste->count() }}</span>
                </p>
                <ul class="mt-1 space-y-0.5 text-sm text-gray-600">
                    @foreach ($liste->take(50) as $a)
                        <li>
                            @if ($a['ligne'])<span class="text-gray-400">L{{ $a['ligne'] }}</span>@endif
                            {{ $a['message'] }}
                        </li>
                    @endforeach
                    @if ($liste->count() > 50)
                        <li class="text-gray-400">… et {{ $liste->count() - 50 }} de plus</li>
                    @endif
                </ul>
            </div>
        @endforeach
    </div>
@endif
