<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Administration</h1>
    </x-slot>

    <x-admin.shell active="dashboard" titre="Vue d'ensemble">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                // [libellé, valeur, détail, route, pictogramme, couleur, couleur du chiffre]
                $tuiles = [
                    ['Sessions', $nbSessions, "{$nbSessionsFpc} FPC · {$nbSessionsOp} OP", 'admin.sessions.index', 'calendrier', 'bleu', 'text-edl-bleu'],
                    ['Stagiaires', $nbStagiaires, $nbStagiairesDisparus ? "{$nbStagiairesDisparus} disparu(s) de l'import" : 'à jour', 'admin.stagiaires.index', 'groupe', 'rose', 'text-edl-rose'],
                    ['Formateurs', $nbFormateurs, null, 'admin.formateurs.index', 'personne', 'orange', 'text-edl-orange-fonce'],
                    ['Sessions sans référent', $sessionsSansFormateur, 'formateur à affecter', 'admin.sessions.index', 'alerte', 'jaune', 'text-edl-jaune-fonce'],
                ];
            @endphp

            @foreach ($tuiles as [$label, $valeur, $detail, $route, $icone, $couleur, $teinteChiffre])
                <a href="{{ route($route) }}" class="rounded-xl border border-edl-sable bg-white p-5 transition hover:shadow-sm">
                    <x-pastille :icone="$icone" :couleur="$couleur" taille="lg" />
                    <p class="mt-3 text-sm text-edl-texte-doux">{{ $label }}</p>
                    <p class="text-3xl font-semibold {{ $teinteChiffre }}">{{ $valeur }}</p>
                    @if ($detail)
                        <p class="mt-1 text-xs text-edl-texte-doux">{{ $detail }}</p>
                    @endif
                </a>
            @endforeach
        </div>

        <section class="flex flex-wrap items-center gap-4 rounded-xl border border-edl-jaune/40 bg-edl-jaune/15 p-5">
            <x-pastille icone="import" couleur="jaune" plein taille="lg" />
            <div class="min-w-0 flex-[1_1_20rem] text-edl-jaune-fonce">
                <h2 class="font-semibold">Import GESCOF</h2>
                @if ($dernierImport)
                    <p class="text-sm">
                        Dernier import appliqué le
                        <strong>{{ $dernierImport->created_at->translatedFormat('d/m/Y à H\hi') }}</strong>
                        ({{ $dernierImport->fichier_nom }}) —
                        {{ $dernierImport->comptes_crees }} compte(s) créé(s),
                        {{ $dernierImport->sessions_creees }} session(s) créée(s).
                    </p>
                @else
                    <p class="text-sm">Aucun import appliqué pour le moment.</p>
                @endif
            </div>
            <a href="{{ route('admin.imports.index') }}"
               class="inline-flex rounded-lg bg-edl-bleu px-4 py-2.5 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                Nouvel import
            </a>
        </section>

        <div class="grid items-start gap-4 lg:grid-cols-2">
            <x-admin.card titre="Dernières ressources importées" icone="dossier" couleur="bleu-vert">
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
                    <p class="mb-2 text-xs text-gray-500">{{ $nbRessourcesTotal }} ressource(s) au total</p>

                    @if ($dernieresRessources->isEmpty())
                        <p class="text-sm text-gray-500">Aucune ressource déposée pour le moment.</p>
                    @else
                        <ul class="divide-y divide-gray-100 text-sm">
                            <template x-for="ressource in ressources.slice(0, 5)" :key="ressource.id">
                                <li class="flex items-center justify-between py-2">
                                    <button type="button" @click="selection = ressource; $dispatch('open-modal', 'ressource-details')"
                                            class="text-left text-edl-bleu hover:underline">
                                        <span x-text="ressource.nom"></span>
                                        <span class="text-xs text-gray-500" x-text="'· ' + ressource.type"></span>
                                    </button>
                                    <span class="text-xs text-gray-500" x-text="ressource.date"></span>
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
                                                <span class="text-xs text-gray-500" x-text="'· ' + ressource.type"></span>
                                            </button>
                                            <span class="text-xs text-gray-500" x-text="ressource.date"></span>
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

            <x-admin.card titre="Alertes" icone="alerte" couleur="rose">
                <ul class="divide-y divide-gray-100 text-sm">
                    <li class="flex items-center justify-between py-2">
                        <span>Purges OP en attente de validation</span>
                        @if ($purgesOpEnAttente > 0)
                            <a href="{{ route('admin.purges.index') }}" class="font-semibold text-edl-rose hover:underline">{{ $purgesOpEnAttente }}</a>
                        @else
                            <span class="text-gray-500">Aucune</span>
                        @endif
                    </li>
                    <li class="flex items-center justify-between py-2">
                        <span>Purges FPC en attente de validation</span>
                        @if ($purgesFpcEnAttente > 0)
                            <a href="{{ route('admin.purges.index') }}" class="font-semibold text-edl-rose hover:underline">{{ $purgesFpcEnAttente }}</a>
                        @else
                            <span class="text-gray-500">Aucune</span>
                        @endif
                    </li>
                </ul>

                <div class="mt-3">
                    <p class="text-xs text-gray-500">
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

            <x-admin.card titre="Avancement des sessions FPC en cours" icone="graphique" couleur="violet">
                @if ($avancementFpc->isEmpty())
                    <p class="text-sm text-gray-500">Aucune session FPC en cours.</p>
                @else
                    <p class="mb-2 text-xs text-gray-500">{{ $avancementFpc->count() }} session(s) FPC en cours</p>
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

            <x-admin.card titre="Questionnaires actifs — taux de réponse" icone="questionnaire" couleur="vert">
                @if ($questionnairesTaux->isEmpty())
                    <p class="text-sm text-gray-500">Aucun questionnaire actif.</p>
                @else
                    <p class="mb-2 text-xs text-gray-500">{{ $questionnairesTaux->count() }} questionnaire(s) actif(s)</p>
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
