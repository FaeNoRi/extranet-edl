<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">
            Séance du {{ $seance->date->format('d/m/Y') }}
        </h2>
    </x-slot>

    <x-formateur.shell active="sessions">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-edl-marron">Dossier de séance — {{ $seance->date->translatedFormat('d F Y') }}</h1>
                <p class="text-sm text-gray-500">
                    <a href="{{ route('formateur.sessions.show', $seance->sessionFormation) }}" class="hover:underline">{{ $seance->sessionFormation->nom }}</a>
                    @if ($seance->stagiaire) · {{ $seance->stagiaire->nom_complet }} @endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('formateur.seances.fiche', $seance) }}"
                   class="rounded-md border border-edl-bleu px-3 py-2 text-sm font-semibold text-edl-bleu hover:bg-edl-bleu/10">
                    Fiche pédagogique (PDF)
                </a>
                <a href="{{ route('formateur.seances.edit', $seance) }}"
                   class="rounded-md bg-edl-orange px-3 py-2 text-sm font-semibold text-white hover:opacity-90">Modifier</a>
                <form method="POST" action="{{ route('formateur.seances.destroy', $seance) }}"
                      onsubmit="return confirm('Supprimer cette séance ?')">
                    @csrf @method('DELETE')
                    <button class="rounded-md border border-edl-rose px-3 py-2 text-sm font-semibold text-edl-rose hover:bg-edl-rose/10">Supprimer</button>
                </form>
            </div>
        </div>

        <x-admin.card titre="Résumé">
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500">Objectifs</dt>
                    <dd>@forelse ($seance->objectifs ?? [] as $o)<span class="block">• {{ $o }}</span>@empty — @endforelse</dd></div>
                <div><dt class="text-gray-500">Outils</dt><dd>{{ $seance->outils ? implode(', ', $seance->outils) : '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-500">Contenu</dt><dd class="whitespace-pre-line">{{ $seance->contenu ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-500">Sources</dt><dd class="whitespace-pre-line">{{ $seance->sources ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-500">Analyse</dt><dd class="whitespace-pre-line">{{ $seance->analyse_seance ?: '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card titre="Ressources de la séance">
            @if ($seance->ressources->isEmpty())
                <p class="text-sm text-gray-500">Aucune ressource.</p>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($seance->ressources as $ressource)
                        <li class="flex items-center justify-between py-2">
                            <span>
                                {{ $ressource->nom }}
                                @if ($ressource->pivot->transmis)
                                    <span class="ml-1 rounded bg-edl-vert-fonce/15 px-1.5 py-0.5 text-xs text-edl-vert-fonce">transmis au stagiaire</span>
                                @else
                                    <span class="ml-1 rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-500">document de travail</span>
                                @endif
                            </span>
                            <a href="{{ route('formateur.ressources.download', $ressource) }}" class="text-edl-bleu hover:underline">Télécharger</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        <x-admin.card titre="Fiches du référentiel (modules vus)">
            @php $refRessources = $seance->referentiels->flatMap->ressources->unique('id'); @endphp
            @if ($seance->referentiels->isEmpty())
                <p class="text-sm text-gray-500">Aucun module coché.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($seance->referentiels as $referentiel)
                        <li>
                            <span class="font-mono text-xs">{{ $referentiel->code }}</span>
                            {{ $referentiel->contenu }}
                            <span class="text-gray-400">— {{ $referentiel->module }} ({{ implode('/', $referentiel->niveaux ?: []) ?: 'tous niveaux' }})</span>
                            @if ($referentiel->ressources->isNotEmpty())
                                <ul class="ml-4 mt-0.5 text-xs text-gray-500">
                                    @foreach ($referentiel->ressources as $r)
                                        <li>↳ {{ $r->nom }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <p class="mt-3 text-xs text-gray-400">
                    Ces fiches sont automatiquement visibles dans l'espace du stagiaire pour cette séance.
                </p>
            @endif
        </x-admin.card>
    </x-formateur.shell>
</x-app-layout>
