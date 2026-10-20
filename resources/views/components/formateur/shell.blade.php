@props(['active' => null, 'titre' => null])

@php
    $liens = [
        'dashboard' => ['route' => 'formateur.dashboard',     'label' => 'Vue d\'ensemble', 'icone' => 'accueil', 'couleur' => 'orange'],
        'sessions'  => ['route' => 'formateur.sessions.index', 'label' => 'Mes sessions', 'icone' => 'groupe', 'couleur' => 'bleu'],
    ];
@endphp

<div class="py-8">
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <aside class="lg:w-60 lg:flex-shrink-0">
            <x-menu-lateral :liens="$liens" :active="$active" espace="formateur" label="Rubriques de l'espace formateur" />
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
