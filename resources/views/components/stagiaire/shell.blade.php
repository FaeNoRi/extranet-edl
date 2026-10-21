@props(['active' => null, 'large' => false])

@php
    $liens = [
        'dashboard'  => ['route' => 'stagiaire.dashboard',       'label' => 'Mon espace', 'icone' => 'accueil', 'couleur' => 'rose'],
        'ressources' => ['route' => 'stagiaire.ressources.index', 'label' => 'Ressources pédagogiques', 'icone' => 'dossier', 'couleur' => 'bleu-vert'],
        'questionnaires' => ['route' => 'stagiaire.questionnaires.index', 'label' => 'Questionnaires', 'icone' => 'questionnaire', 'couleur' => 'jaune'],
    ];
@endphp

<div class="py-8">
    <div @class([
        'mx-auto flex flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8',
        'max-w-5xl' => ! $large,
        'max-w-7xl' => $large,
    ])>
        <aside class="lg:w-60 lg:flex-shrink-0">
            <x-menu-lateral :liens="$liens" :active="$active" espace="stagiaire" label="Rubriques de mon espace" />
        </aside>

        <div class="min-w-0 flex-1 space-y-6">
            @if (session('succes'))
                <div role="status" class="rounded-md border border-edl-vert-fonce/30 bg-edl-vert-fonce/10 px-4 py-3 text-sm text-edl-vert-fonce">
                    {{ session('succes') }}
                </div>
            @endif

            <x-form-errors />

            {{ $slot }}
        </div>
    </div>
</div>
