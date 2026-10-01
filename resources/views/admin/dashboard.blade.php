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

        <div class="grid items-start gap-4 lg:grid-cols-2">
            <x-admin.card titre="Dernières ressources importées">
                <div x-data="{
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
                    <p class="mb-2 text-xs text-gray-400">{{ $nbRessourcesTotal }} ressource(s) au total</p>

                    @if ($dernieresRessources->isEmpty())
                        <p class="text-sm text-gray-500">Aucune ressource déposée pour le moment.</p>
                    @else
                        <ul class="divide-y divide-gray-100 text-sm">
                            <template x-for="ressource in ressources.slice(0, 5)" :key="ressource.id">
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
                        <x-admin.voir-plus :restant="min(10, max(0, $dernieresRessources->count() - 5))">
                            <x-slot name="plus">
                                <ul class="divide-y divide-gray-100 text-sm">
                                    <template x-for="ressource in ressources.slice(5, 15)" :key="ressource.id">
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
                            </x-slot>
                        </x-admin.voir-plus>
                    @endif

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

            <x-admin.card titre="Alertes">
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
                </ul>

                <div class="mt-3">
                    <p class="text-xs text-gray-400">
                        Sessions FPC sans séance depuis 3 semaines :
                        {{ $sessionsDecrochees->isEmpty() ? 'aucune' : $sessionsDecrochees->count() }}
                    </p>
                    @if ($sessionsDecrochees->isNotEmpty())
                        <ul class="mt-1 space-y-0.5 pl-3 text-xs text-gray-500">
                            @foreach ($sessionsDecrochees->take(5) as $session)
                                @include('admin.dashboard._session_decrochee_ligne', ['session' => $session])
                            @endforeach
                        </ul>
                        <x-admin.voir-plus :restant="min(10, max(0, $sessionsDecrochees->count() - 5))">
                            <x-slot name="plus">
                                <ul class="space-y-0.5 pl-3 text-xs text-gray-500">
                                    @foreach ($sessionsDecrochees->slice(5, 10) as $session)
                                        @include('admin.dashboard._session_decrochee_ligne', ['session' => $session])
                                    @endforeach
                                </ul>
                            </x-slot>
                        </x-admin.voir-plus>
                    @endif
                </div>
            </x-admin.card>

            <x-admin.card titre="Avancement des sessions FPC en cours">
                @if ($avancementFpc->isEmpty())
                    <p class="text-sm text-gray-500">Aucune session FPC en cours.</p>
                @else
                    <p class="mb-2 text-xs text-gray-400">{{ $avancementFpc->count() }} session(s) FPC en cours</p>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($avancementFpc->take(5) as $ligne)
                            @include('admin.dashboard._avancement_ligne', ['ligne' => $ligne])
                        @endforeach
                    </ul>
                    <x-admin.voir-plus :restant="min(10, max(0, $avancementFpc->count() - 5))">
                        <x-slot name="plus">
                            <ul class="divide-y divide-gray-100 text-sm">
                                @foreach ($avancementFpc->slice(5, 10) as $ligne)
                                    @include('admin.dashboard._avancement_ligne', ['ligne' => $ligne])
                                @endforeach
                            </ul>
                        </x-slot>
                    </x-admin.voir-plus>
                @endif
            </x-admin.card>

            <x-admin.card titre="Questionnaires actifs — taux de réponse">
                @if ($questionnairesTaux->isEmpty())
                    <p class="text-sm text-gray-500">Aucun questionnaire actif.</p>
                @else
                    <p class="mb-2 text-xs text-gray-400">{{ $questionnairesTaux->count() }} questionnaire(s) actif(s)</p>
                    <ul class="divide-y divide-gray-100 text-sm">
                        @foreach ($questionnairesTaux->take(5) as $ligne)
                            @include('admin.dashboard._questionnaire_ligne', ['ligne' => $ligne])
                        @endforeach
                    </ul>
                    <x-admin.voir-plus :restant="min(10, max(0, $questionnairesTaux->count() - 5))">
                        <x-slot name="plus">
                            <ul class="divide-y divide-gray-100 text-sm">
                                @foreach ($questionnairesTaux->slice(5, 10) as $ligne)
                                    @include('admin.dashboard._questionnaire_ligne', ['ligne' => $ligne])
                                @endforeach
                            </ul>
                        </x-slot>
                    </x-admin.voir-plus>
                @endif
            </x-admin.card>
        </div>
    </x-admin.shell>
</x-app-layout>
