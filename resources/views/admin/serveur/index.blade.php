@php
    $libelles = [
        \App\Services\PreflightService::OK => ['OK', 'bg-edl-vert-fonce/10 text-edl-vert-fonce'],
        \App\Services\PreflightService::AVERTISSEMENT => ['À traiter', 'bg-edl-jaune/15 text-edl-jaune-fonce'],
        \App\Services\PreflightService::BLOQUANT => ['Bloquant', 'bg-edl-rose/10 text-edl-rose-fonce'],
    ];
    $bloquants = collect($controles)->where('statut', \App\Services\PreflightService::BLOQUANT)->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Serveur</h1>
    </x-slot>

    <x-admin.shell active="serveur">
        <x-admin.card icone="serveur" couleur="marron">
            <x-slot name="titre">Base de données</x-slot>

            @if ($migrations === [])
                <p class="text-sm text-gray-700">La base est à jour.</p>
            @else
                <p class="text-sm text-gray-700">
                    {{ count($migrations) }} mise(s) à jour en attente : à appliquer juste après le dépôt d'une nouvelle version.
                </p>
                <ul class="mt-2 list-inside list-disc text-sm text-gray-500">
                    @foreach ($migrations as $migration)
                        <li>{{ $migration }}</li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('admin.serveur.migrer') }}" class="mt-4 space-y-3"
                      x-data @submit="if (! confirm('Mettre à jour la base de données ?')) $event.preventDefault()">
                    @csrf
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="sauvegarder" value="1" checked
                               class="rounded border-gray-300 text-edl-bleu focus:ring-edl-bleu">
                        Sauvegarder la base avant
                    </label>
                    <x-primary-button>Mettre à jour la base</x-primary-button>
                </form>
            @endif
        </x-admin.card>

        <x-admin.card icone="coche" couleur="vert">
            <x-slot name="titre">Contrôles de mise en service</x-slot>

            <p class="mb-4 text-sm text-gray-700">
                @if ($bloquants > 0)
                    {{ $bloquants }} point(s) bloquant(s).
                @else
                    Aucun point bloquant.
                @endif
                Pendant la phase de test, « Envoi d'e-mails réel » reste bloquant tant que les e-mails sont coupés.
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="py-2 pr-3">Contrôle</th>
                            <th class="py-2 pr-3">Résultat</th>
                            <th class="py-2">Détail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($controles as $controle)
                            <tr>
                                <td class="py-2 pr-3 font-medium text-gray-800">{{ $controle['nom'] }}</td>
                                <td class="whitespace-nowrap py-2 pr-3">
                                    <span class="rounded px-1.5 py-0.5 text-xs {{ $libelles[$controle['statut']][1] }}">{{ $libelles[$controle['statut']][0] }}</span>
                                </td>
                                <td class="py-2 text-gray-500">{{ $controle['statut'] === \App\Services\PreflightService::OK ? '' : $controle['detail'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <x-admin.card icone="horloge" couleur="bleu">
            <x-slot name="titre">Tâches planifiées et sauvegardes</x-slot>

            <p class="text-sm text-gray-700">
                Dernier passage du planificateur :
                {{ $temoin ? $temoin->format('d/m/Y à H:i') : 'aucun' }}.
            </p>

            @if ($rapport)
                <h3 class="mt-4 font-semibold text-gray-800">
                    Dernières tâches exécutées ({{ \Illuminate\Support\Carbon::parse($rapport['le'])->format('d/m/Y à H:i') }})
                </h3>
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach ($rapport['taches'] as $tache)
                        <li>
                            <span class="rounded px-1.5 py-0.5 text-xs {{ $tache['ok'] ? 'bg-edl-vert-fonce/10 text-edl-vert-fonce' : 'bg-edl-rose/10 text-edl-rose-fonce' }}">
                                {{ $tache['ok'] ? 'OK' : 'Échec' }}
                            </span>
                            <code class="ml-1 text-gray-800">{{ $tache['commande'] }}</code>
                            @unless ($tache['ok'])
                                <pre class="mt-1 whitespace-pre-wrap rounded bg-gray-50 p-2 text-xs text-gray-700">{{ $tache['sortie'] }}</pre>
                            @endunless
                        </li>
                    @endforeach
                </ul>
            @endif

            <h3 class="mt-4 font-semibold text-gray-800">Sauvegardes sur le serveur</h3>
            @if ($sauvegardes === [])
                <p class="mt-2 text-sm text-gray-500">Aucune archive.</p>
            @else
                <ul class="mt-2 text-sm text-gray-700">
                    @foreach ($sauvegardes as $sauvegarde)
                        <li>{{ $sauvegarde['le']->format('d/m/Y H:i') }} — {{ number_format($sauvegarde['taille'] / 1048576, 1, ',', ' ') }} Mo</li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        <x-admin.card icone="globe" couleur="bleu-vert">
            <x-slot name="titre">Connexion et PHP</x-slot>

            <p class="mb-3 text-sm text-gray-700">
                Si un en-tête X-Forwarded-For est présent et que la connexion n'est pas vue comme HTTPS, l'hébergeur place
                l'extranet derrière un proxy : renseigner <code>TRUSTED_PROXIES</code> dans le <code>.env</code> avec l'adresse de connexion.
            </p>

            <dl class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-[auto_1fr]">
                @foreach ($requete + $php as $libelle => $valeur)
                    <dt class="font-medium text-gray-800">{{ $libelle }}</dt>
                    <dd class="break-all text-gray-500">{{ $valeur }}</dd>
                @endforeach
            </dl>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
