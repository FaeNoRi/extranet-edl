@php
    $routeDocument = auth()->user()->isStagiaireOp() ? 'stagiaire.documents.apercu' : 'stagiaire.documents.download';
@endphp

<x-app-layout>
    @if ($session)
        <x-slot name="sousTitre">
            {{ collect([$session->langue, $session->code_produit?->value, $session->num_GESCOF ? 'Session '.$session->num_GESCOF : null])->filter()->implode(' · ') }}
        </x-slot>
    @endif
    <x-slot name="header">
        <h1 class="text-2xl font-semibold leading-tight sm:text-3xl">Mon espace</h1>
    </x-slot>

    <x-stagiaire.shell active="dashboard">
        {{-- Bandeau de bienvenue --}}
        <section class="flex flex-wrap items-center gap-6 rounded-2xl border border-edl-sable bg-white p-6">
            <div class="min-w-0 flex-[1_1_18rem]">
                <p class="text-edl-texte-doux">Bienvenue sur votre espace,</p>
                <h2 class="mt-0.5 text-2xl font-semibold sm:text-3xl">{{ $stagiaire->nom_complet }}</h2>
                @if ($session)
                    <p class="mt-2 text-lg font-semibold text-edl-bleu">{{ $session->nom }}</p>
                @else
                    <p class="mt-2 text-edl-texte-doux">Aucune session n'est rattachée à votre compte.</p>
                @endif

                @if ($session?->distanciel && $session->lien_teams)
                    <a href="{{ $session->lien_teams }}" target="_blank" rel="noopener"
                       class="mt-4 inline-flex items-center gap-2 rounded-lg bg-edl-bleu px-4 py-2.5 text-sm font-semibold text-white hover:bg-edl-vert-fonce">
                        <x-icone nom="video" />
                        Rejoindre la session Teams<span class="sr-only"> (nouvel onglet)</span>
                    </a>
                @endif
            </div>

            @if ($session?->formateur)
                <div class="flex flex-[1_1_16rem] items-start gap-4 rounded-xl bg-edl-orange/15 p-4">
                    @if ($session->formateur->photo_path)
                        <img src="{{ Storage::url($session->formateur->photo_path) }}" alt="" loading="lazy" class="h-14 w-14 flex-none rounded-full object-cover">
                    @else
                        <span class="flex h-14 w-14 flex-none items-center justify-center rounded-full bg-edl-orange text-xl font-semibold text-gray-900" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr($session->formateur->prenom ?? '', 0, 1).mb_substr($session->formateur->nom ?? '', 0, 1)) }}
                        </span>
                    @endif
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wider text-edl-orange-fonce">Votre formateur</p>
                        <p class="text-lg font-semibold">{{ $session->formateur->nom_complet }}</p>
                        @if ($session->formateur->presentation)
                            <p class="mt-1 text-sm">{{ $session->formateur->presentation }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        {{-- Repères --}}
        @if ($session)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-4 rounded-xl border border-edl-sable bg-white p-4">
                    <x-pastille icone="calendrier" couleur="bleu-vert" taille="lg" />
                    <div>
                        <p class="text-sm text-edl-texte-doux">Prochaine séance</p>
                        <p class="text-lg font-semibold">
                            @if (! $prochaineSeance)
                                Aucune prévue
                            @elseif ($prochaineSeance->date->isToday())
                                Aujourd'hui
                            @else
                                {{ ucfirst($prochaineSeance->date->translatedFormat('l j F')) }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-edl-sable bg-white p-4">
                    <div class="flex items-center gap-4">
                        <x-pastille icone="coche" couleur="vert" taille="lg" />
                        <div>
                            <p class="text-sm text-edl-texte-doux">Séances réalisées</p>
                            <p class="text-lg font-semibold">{{ $nbSeancesRealisees }} sur {{ $planning->count() }}</p>
                        </div>
                    </div>
                    @if ($planning->isNotEmpty())
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-edl-vert/15" aria-hidden="true">
                            <div class="h-2 rounded-full bg-edl-vert" style="width: {{ round(100 * $nbSeancesRealisees / $planning->count()) }}%"></div>
                        </div>
                    @endif
                </div>

                <a href="{{ route('stagiaire.questionnaires.index') }}"
                   @class([
                       'flex items-center gap-4 rounded-xl border p-4 transition hover:shadow-sm',
                       'border-edl-jaune/40 bg-edl-jaune/15' => $nbQuestionnairesAFaire > 0,
                       'border-edl-sable bg-white' => $nbQuestionnairesAFaire === 0,
                   ])>
                    <x-pastille icone="crayon" couleur="jaune" taille="lg" :plein="$nbQuestionnairesAFaire > 0" />
                    <div>
                        <p class="text-sm text-edl-jaune-fonce">Questionnaires</p>
                        <p class="text-lg font-semibold">
                            {{ $nbQuestionnairesAFaire > 0 ? $nbQuestionnairesAFaire.' à remplir' : 'Tout est rempli' }}
                        </p>
                    </div>
                </a>
            </div>
        @endif

        {{-- Documents : 2 rectangles --}}
        <div class="grid gap-4 md:grid-cols-2">
            <x-admin.card titre="Présentation de la structure" icone="globe" couleur="bleu">
                @if ($documentsStructure->isEmpty())
                    <p class="text-sm text-edl-texte-doux">Aucun document.</p>
                @else
                    <ul class="-my-2 text-sm">
                        @foreach ($documentsStructure as $doc)
                            <li>
                                <a href="{{ route($routeDocument, $doc) }}" class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-2.5 hover:bg-edl-creme">
                                    <x-badge-fichier :fichier="$doc->nom_fichier_original ?: $doc->chemin_fichier" />
                                    <span class="flex-1 text-edl-bleu">{{ $doc->nom }}</span>
                                    <x-icone nom="chevron" class="h-4 w-4 text-edl-bleu" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            <x-admin.card titre="Mes documents" icone="document" couleur="violet">
                @if ($mesDocuments->isEmpty())
                    <p class="text-sm text-edl-texte-doux">Aucun document pour votre session.</p>
                @else
                    <ul class="-my-2 text-sm">
                        @foreach ($mesDocuments as $doc)
                            <li>
                                <a href="{{ route($routeDocument, $doc) }}" class="-mx-2 flex items-center gap-3 rounded-lg px-2 py-2.5 hover:bg-edl-creme">
                                    <x-badge-fichier :fichier="$doc->nom_fichier_original ?: $doc->chemin_fichier" />
                                    <span class="flex-1 text-edl-bleu">{{ $doc->nom }}</span>
                                    <x-icone nom="chevron" class="h-4 w-4 text-edl-bleu" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        </div>

        {{-- Planning (surtout OP) --}}
        @if ($planning->isNotEmpty())
            <x-admin.card titre="Planning des séances" icone="calendrier" couleur="bleu-vert">
                <ul class="mb-4 flex flex-wrap gap-4 text-sm text-edl-texte-doux" aria-label="Légende">
                    <li class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-edl-vert" aria-hidden="true"></span>réalisée</li>
                    <li class="flex items-center gap-1.5"><span class="h-3 w-3 rounded bg-edl-rose" aria-hidden="true"></span>aujourd'hui</li>
                    <li class="flex items-center gap-1.5"><span class="h-3 w-3 rounded border-2 border-edl-bleu-vert" aria-hidden="true"></span>à venir</li>
                </ul>

                <div class="space-y-4">
                    @foreach ($planning->groupBy(fn ($jour) => $jour->date->format('Y-m')) as $jours)
                        <div>
                            <h3 class="mb-2 text-sm uppercase tracking-wider text-edl-texte-doux">{{ ucfirst($jours->first()->date->translatedFormat('F Y')) }}</h3>
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($jours as $jour)
                                    @php
                                        $etat = $jour->date->isToday() ? 'aujourd\'hui' : ($jour->date->isPast() ? 'réalisée' : 'à venir');
                                    @endphp
                                    <li @class([
                                        'min-w-[3.5rem] rounded-lg px-2 py-1 text-center tabular-nums',
                                        'border border-edl-vert/40 bg-edl-vert/15 text-edl-vert-fonce' => $etat === 'réalisée',
                                        'border border-edl-rose bg-edl-rose text-white' => $etat === 'aujourd\'hui',
                                        'border-2 border-edl-bleu-vert bg-white' => $etat === 'à venir',
                                    ])>
                                        <span class="block text-xs">{{ $jour->date->translatedFormat('D') }}</span>
                                        <span class="block text-lg font-semibold leading-tight">{{ $jour->date->format('d') }}</span>
                                        <span class="sr-only">{{ $jour->date->translatedFormat('F') }}, {{ $etat }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        @endif

        <a href="{{ route('stagiaire.ressources.index') }}" class="flex items-center gap-4 rounded-xl bg-edl-bleu p-5 text-white transition hover:bg-edl-bleu-fonce">
            <span class="flex h-11 w-11 flex-none items-center justify-center rounded-xl bg-white text-edl-bleu" aria-hidden="true">
                <x-icone nom="dossier" class="h-6 w-6" />
            </span>
            <span class="flex-1">
                <span class="block text-lg font-semibold">Mes ressources pédagogiques</span>
                <span class="block text-sm">Les documents transmis après chaque séance</span>
            </span>
            <x-icone nom="fleche" class="h-6 w-6" />
        </a>
    </x-stagiaire.shell>
</x-app-layout>
