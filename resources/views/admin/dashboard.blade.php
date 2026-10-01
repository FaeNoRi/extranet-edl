<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-edl-marron">Administration</h2>
    </x-slot>

    <x-admin.shell active="dashboard" titre="Vue d'ensemble">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tuiles = [
                    ['Sessions', $nbSessions, "{$nbSessionsFpc} FPC · {$nbSessionsOp} OP", 'admin.sessions.index'],
                    ['Stagiaires', $nbStagiaires, $nbStagiairesDisparus ? "{$nbStagiairesDisparus} disparu(s) de l'import" : 'à jour', 'admin.stagiaires.index'],
                    ['Formateurs', $nbFormateurs, null, 'admin.formateurs.index'],
                    ['Sessions sans référent', $sessionsSansFormateur, 'formateur à affecter', 'admin.sessions.index'],
                ];
            @endphp

            @foreach ($tuiles as [$label, $valeur, $detail, $route])
                <a href="{{ route($route) }}" class="rounded-lg bg-white p-5 shadow-sm transition hover:shadow">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-1 text-3xl font-semibold text-edl-bleu">{{ $valeur }}</p>
                    @if ($detail)
                        <p class="mt-1 text-xs text-gray-400">{{ $detail }}</p>
                    @endif
                </a>
            @endforeach
        </div>

        <x-admin.card>
            <div x-data="{
                    ouvert: false,
                    selection: null,
                    ressources: {{ Illuminate\Support\Js::from($dernieresRessources->map(fn ($r) => [
                        'id' => $r->id,
                        'nom' => $r->nom,
                        'type' => $r->type_fichier,
                        'uploader' => $r->uploader?->nom_complet,
                        'session' => $r->sessionFormation?->nom,
                        'date' => $r->created_at->translatedFormat('d/m/Y à H\hi'),
                        'telechargements' => $r->nb_telechargement,
                        'urlTelecharger' => route('admin.ressources.download', $r),
                        'urlSupprimer' => route('admin.ressources.destroy', $r),
                    ])->values()) }},
                 }">
                <button type="button" @click="ouvert = !ouvert" class="flex w-full items-center justify-between text-left">
                    <span class="font-semibold text-gray-800">Dernières ressources importées</span>
                    <span class="text-gray-400" x-text="ouvert ? '−' : '+'"></span>
                </button>

                <div x-show="ouvert" class="mt-3">
                    @if ($dernieresRessources->isEmpty())
                        <p class="text-sm text-gray-500">Aucune ressource déposée pour le moment.</p>
                    @else
                        <p class="mb-2 text-xs text-gray-400">
                            {{ $nbRessourcesTotal }} ressource(s) au total
                            @if ($nbRessourcesTotal > $dernieresRessources->count())
                                — {{ $dernieresRessources->count() }} plus récente(s) affichée(s)
                            @endif
                        </p>
                        <ul class="divide-y divide-gray-100 text-sm">
                            <template x-for="ressource in ressources" :key="ressource.id">
                                <li class="flex items-center justify-between py-2">
                                    <button type="button" @click="selection = ressource; $dispatch('open-modal', 'ressource-details')"
                                            class="text-left text-edl-bleu hover:underline">
                                        <span x-text="ressource.nom"></span>
                                        <span class="text-xs text-gray-400" x-text="'· ' + ressource.type"></span>
                                    </button>
                                    <span class="text-xs text-gray-400" x-text="ressource.date"></span>
                                </li>
                            </template>
                        </ul>
                    @endif
                </div>

                <x-modal name="ressource-details">
                    <div class="p-6" x-show="selection">
                        <h3 class="text-lg font-semibold text-edl-marron" x-text="selection?.nom"></h3>
                        <dl class="mt-3 space-y-1 text-sm">
                            <div><dt class="inline text-gray-500">Type :</dt> <dd class="inline" x-text="selection?.type"></dd></div>
                            <div><dt class="inline text-gray-500">Déposé par :</dt> <dd class="inline" x-text="selection?.uploader ?? '—'"></dd></div>
                            <div><dt class="inline text-gray-500">Session :</dt> <dd class="inline" x-text="selection?.session ?? '—'"></dd></div>
                            <div><dt class="inline text-gray-500">Déposé le :</dt> <dd class="inline" x-text="selection?.date"></dd></div>
                            <div><dt class="inline text-gray-500">Téléchargements :</dt> <dd class="inline" x-text="selection?.telechargements"></dd></div>
                        </dl>
                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button" @click="$dispatch('close-modal', 'ressource-details')"
                                    class="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Fermer
                            </button>
                            <a :href="selection?.urlTelecharger" class="rounded-md bg-edl-bleu px-3 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                                Télécharger
                            </a>
                            <form method="POST" :action="selection?.urlSupprimer" @submit="if (! confirm('Supprimer cette ressource ?')) $event.preventDefault()">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-md border border-edl-rose px-3 py-2 text-sm font-semibold text-edl-rose hover:bg-edl-rose/10">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </x-modal>
            </div>
        </x-admin.card>

        <x-admin.card titre="Import GESCOF">
            @if ($dernierImport)
                <p class="text-sm text-gray-600">
                    Dernier import appliqué le
                    <strong>{{ $dernierImport->created_at->translatedFormat('d/m/Y à H\hi') }}</strong>
                    ({{ $dernierImport->fichier_nom }}) —
                    {{ $dernierImport->comptes_crees }} compte(s) créé(s),
                    {{ $dernierImport->sessions_creees }} session(s) créée(s).
                </p>
            @else
                <p class="text-sm text-gray-500">Aucun import appliqué pour le moment.</p>
            @endif

            <div class="mt-4">
                <a href="{{ route('admin.imports.index') }}"
                   class="inline-flex rounded-md bg-edl-bleu px-4 py-2 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                    Nouvel import
                </a>
            </div>
        </x-admin.card>

        <x-admin.card>
            <x-admin.accordion titre="Alertes">
                <ul class="divide-y divide-gray-100 text-sm">
                    <li class="flex items-center justify-between py-2">
                        <span>Purges OP en attente de validation</span>
                        @if ($purgesOpEnAttente > 0)
                            <a href="{{ route('admin.purges.index') }}" class="font-semibold text-edl-rose hover:underline">{{ $purgesOpEnAttente }}</a>
                        @else
                            <span class="text-gray-400">Aucune</span>
                        @endif
                    </li>
                    <li class="flex items-center justify-between py-2">
                        <span>Purges FPC en attente de validation</span>
                        @if ($purgesFpcEnAttente > 0)
                            <a href="{{ route('admin.purges.index') }}" class="font-semibold text-edl-rose hover:underline">{{ $purgesFpcEnAttente }}</a>
                        @else
                            <span class="text-gray-400">Aucune</span>
                        @endif
                    </li>
                    <li class="py-2">
                        <div class="flex items-center justify-between">
                            <span>Sessions FPC sans séance depuis 3 semaines</span>
                            @if ($sessionsDecrochees->isEmpty())
                                <span class="text-gray-400">Aucune</span>
                            @else
                                <span class="font-semibold text-edl-rose">{{ $sessionsDecrochees->count() }}</span>
                            @endif
                        </div>
                        @if ($sessionsDecrochees->isNotEmpty())
                            @php $sessionsDecrocheesAffichees = $sessionsDecrochees->take(5); @endphp
                            <ul class="mt-1 space-y-0.5 pl-3 text-xs text-gray-500">
                                @foreach ($sessionsDecrocheesAffichees as $session)
                                    <li>
                                        <a href="{{ route('admin.sessions.show', $session) }}" class="hover:underline">
                                            {{ $session->nom }}@if ($session->formateur) · {{ $session->formateur->nom_complet }} @endif
                                        </a>
                                    </li>
                                @endforeach
                                @if ($sessionsDecrochees->count() > 5)
                                    <li class="text-gray-400">+ {{ $sessionsDecrochees->count() - 5 }} autre(s)</li>
                                @endif
                            </ul>
                        @endif
                    </li>
                </ul>
            </x-admin.accordion>
        </x-admin.card>

        <x-admin.card>
            <x-admin.accordion titre="Avancement des sessions FPC en cours">
                @if ($avancementFpc->isEmpty())
                    <p class="text-sm text-gray-500">Aucune session FPC en cours.</p>
                @else
                    @php $avancementAffiche = $avancementFpc->take(5); @endphp
                    <p class="mb-2 text-xs text-gray-400">
                        {{ $avancementFpc->count() }} session(s) FPC en cours
                        @if ($avancementFpc->count() > 5)
                            — 5 plus en retard affichées
                        @endif
                    </p>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($avancementAffiche as $ligne)
                            <li class="py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <a href="{{ route('admin.sessions.show', $ligne['session']) }}" class="font-medium text-edl-bleu hover:underline">
                                        {{ $ligne['session']->nom }}
                                    </a>
                                    <span class="whitespace-nowrap text-xs text-gray-500">
                                        {{ $ligne['realises'] }}/{{ $ligne['planifies'] }} jour(s)
                                        @if ($ligne['taux'] !== null) · {{ $ligne['taux'] }}% @endif
                                    </span>
                                </div>
                                @if ($ligne['taux'] !== null)
                                    <div class="mt-1 h-1.5 w-full rounded-full bg-gray-100">
                                        <div class="h-1.5 rounded-full bg-edl-vert-fonce" style="width: {{ min(100, $ligne['taux']) }}%"></div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.accordion>
        </x-admin.card>

        <x-admin.card>
            <x-admin.accordion titre="Questionnaires actifs — taux de réponse">
                @if ($questionnairesTaux->isEmpty())
                    <p class="text-sm text-gray-500">Aucun questionnaire actif.</p>
                @else
                    @php $questionnairesAffiches = $questionnairesTaux->take(5); @endphp
                    <p class="mb-2 text-xs text-gray-400">
                        {{ $questionnairesTaux->count() }} questionnaire(s) actif(s)
                        @if ($questionnairesTaux->count() > 5)
                            — 5 taux de réponse les plus faibles affichés
                        @endif
                    </p>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($questionnairesAffiches as $ligne)
                            <li class="flex items-center justify-between py-2">
                                <a href="{{ route('admin.questionnaires.resultats', $ligne['questionnaire']) }}" class="text-edl-bleu hover:underline">
                                    {{ $ligne['questionnaire']->titre }}
                                    <span class="text-xs text-gray-400">· {{ $ligne['questionnaire']->sessionFormation?->nom ?? 'commun' }}</span>
                                </a>
                                <span class="whitespace-nowrap text-xs text-gray-500">
                                    {{ $ligne['reponses'] }}/{{ $ligne['eligibles'] }}
                                    @if ($ligne['taux'] !== null) · {{ $ligne['taux'] }}% @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.accordion>
        </x-admin.card>
    </x-admin.shell>
</x-app-layout>
