<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Session</h2>
    </x-slot>

    <x-formateur.shell active="sessions">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-edl-marron">{{ $session->nom }}</h1>
                <p class="text-sm text-gray-500">
                    <span class="font-mono">{{ $session->num_GESCOF }}</span> ·
                    {{ $session->code_produit->value }} · {{ $session->langue }}
                    @if ($session->client) · {{ $session->client->nom }} @endif
                </p>
            </div>
            <a href="{{ route('formateur.seances.create', $session) }}"
               class="rounded-md bg-edl-orange px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
                Nouvelle fiche pédagogique
            </a>
        </div>

        @if ($session->distanciel && $session->lien_teams)
            <x-admin.card>
                <a href="{{ $session->lien_teams }}" target="_blank" rel="noopener"
                   class="text-sm font-medium text-edl-bleu hover:underline">Rejoindre la session Teams →</a>
            </x-admin.card>
        @endif

        @if ($parStagiaire)
            <x-admin.card titre="Suivi de progression (FPC)">
                <div class="space-y-4">
                    @foreach ($session->stagiaires as $stagiaire)
                        @php $sesFiches = $parStagiaire->get($stagiaire->id, collect()); @endphp
                        <div>
                            <p class="text-sm font-medium text-gray-800">
                                {{ $stagiaire->nom_complet }}
                                <span class="text-gray-400">— {{ $sesFiches->count() }} fiche(s)</span>
                            </p>
                            @if ($sesFiches->isNotEmpty())
                                <ul class="mt-1 flex flex-wrap gap-2 text-xs">
                                    @foreach ($sesFiches->sortByDesc('date') as $fiche)
                                        <li>
                                            <a href="{{ route('formateur.seances.show', $fiche) }}"
                                               class="rounded border border-gray-200 px-2 py-1 tabular-nums hover:border-edl-orange/40">
                                                {{ $fiche->date->format('d/m/Y') }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        @else
            <x-admin.card titre="Séances ({{ $session->seances->count() }})">
                @if ($session->seances->isEmpty())
                    <p class="text-sm text-gray-500">Aucune séance. Créez la première fiche pédagogique.</p>
                @else
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($session->seances as $seance)
                            <li class="flex items-center justify-between py-2">
                                <span class="font-medium tabular-nums">{{ $seance->date->format('d/m/Y') }}</span>
                                <a href="{{ route('formateur.seances.show', $seance) }}" class="text-edl-bleu hover:underline">Ouvrir la fiche</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        @endif

        <x-admin.card titre="Ressources de la session">
            <form method="POST" action="{{ route('formateur.sessions.ressources.store', $session) }}"
                  enctype="multipart/form-data" class="mb-4 flex flex-wrap items-end gap-3">
                @csrf
                <input type="file" name="fichiers[]" multiple required
                       class="text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-edl-bleu file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                <x-primary-button>Déposer</x-primary-button>
            </form>

            @php $ressources = \App\Models\Ressource::where('session_formation_id', $session->id)->orderBy('nom')->get(); @endphp
            @if ($ressources->isEmpty())
                <p class="text-sm text-gray-500">Aucune ressource déposée.</p>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($ressources as $ressource)
                        <li class="flex items-center justify-between py-2">
                            <span>{{ $ressource->nom }} <span class="text-xs text-gray-400">· {{ $ressource->type_fichier }} · {{ number_format($ressource->taille / 1024, 0, ',', ' ') }} Ko</span></span>
                            <span class="flex gap-3">
                                <a href="{{ route('formateur.ressources.download', $ressource) }}" class="text-edl-bleu hover:underline">Télécharger</a>
                                <form method="POST" action="{{ route('formateur.ressources.destroy', $ressource) }}"
                                      onsubmit="return confirm('Supprimer cette ressource ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-edl-rose hover:underline">Suppr.</button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </x-formateur.shell>
</x-app-layout>
