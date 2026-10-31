@props(['active' => null, 'titre' => null])

@php
    $liens = [
        'dashboard'  => ['route' => 'admin.dashboard',       'label' => 'Vue d\'ensemble', 'icone' => 'accueil', 'couleur' => 'bleu'],
        'imports'    => ['route' => 'admin.imports.index',    'label' => 'Import GESCOF', 'icone' => 'import', 'couleur' => 'jaune'],
        'sessions'   => ['route' => 'admin.sessions.index',   'label' => 'Sessions', 'icone' => 'calendrier', 'couleur' => 'bleu'],
        'formateurs' => ['route' => 'admin.formateurs.index', 'label' => 'Formateurs', 'icone' => 'personne', 'couleur' => 'orange'],
        'stagiaires' => ['route' => 'admin.stagiaires.index', 'label' => 'Stagiaires', 'icone' => 'groupe', 'couleur' => 'rose'],
        'referentiel' => ['route' => 'admin.referentiel.index', 'label' => 'Référentiel', 'icone' => 'livre', 'couleur' => 'vert'],
        'documents'  => ['route' => 'admin.documents.index',  'label' => 'Documents', 'icone' => 'document', 'couleur' => 'bleu-vert'],
        'questionnaires' => ['route' => 'admin.questionnaires.index', 'label' => 'Questionnaires', 'icone' => 'questionnaire', 'couleur' => 'jaune'],
        'purges'     => ['route' => 'admin.purges.index',     'label' => 'Purges', 'icone' => 'corbeille', 'couleur' => 'marron'],
        'journal'    => ['route' => 'admin.journal.index',    'label' => 'Journal des actions', 'icone' => 'journal', 'couleur' => 'violet'],
        'rgpd'       => ['route' => 'admin.rgpd.registre',    'label' => 'RGPD', 'icone' => 'bouclier', 'couleur' => 'vert'],
    ];
@endphp

<div class="py-8">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <aside class="lg:w-60 lg:flex-shrink-0">
            <x-menu-lateral :liens="$liens" :active="$active" espace="admin" label="Rubriques de l'administration" />
        </aside>

        <div class="min-w-0 flex-1 space-y-6">
            @if ($titre)
                <h2 class="text-2xl font-semibold">{{ $titre }}</h2>
            @endif

            @if (session('succes'))
                <div role="status" class="rounded-md border border-edl-vert-fonce/30 bg-edl-vert-fonce/10 px-4 py-3 text-sm text-edl-vert-fonce">
                    {{ session('succes') }}
                </div>
            @endif

            @if (session('erreur'))
                <div role="alert" class="rounded-md border border-edl-rose/30 bg-edl-rose/10 px-4 py-3 text-sm text-edl-rose">
                    {{ session('erreur') }}
                </div>
            @endif

            <x-form-errors />

            {{ $slot }}
        </div>
    </div>
</div>
