@props(['active' => null, 'titre' => null])

@php
    $liens = [
        'dashboard'  => ['route' => 'admin.dashboard',       'label' => 'Vue d\'ensemble'],
        'imports'    => ['route' => 'admin.imports.index',    'label' => 'Import GESCOF'],
        'sessions'   => ['route' => 'admin.sessions.index',   'label' => 'Sessions'],
        'formateurs' => ['route' => 'admin.formateurs.index', 'label' => 'Formateurs'],
        'stagiaires' => ['route' => 'admin.stagiaires.index', 'label' => 'Stagiaires'],
        'purges'     => ['route' => 'admin.purges.index',     'label' => 'Purges'],
        'journal'    => ['route' => 'admin.journal.index',    'label' => 'Journal des actions'],
    ];
@endphp

<div class="py-8">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <aside class="lg:w-56 lg:flex-shrink-0">
            <nav class="flex gap-1 overflow-x-auto rounded-lg bg-white p-2 shadow-sm lg:flex-col lg:overflow-visible">
                @foreach ($liens as $cle => $lien)
                    <a href="{{ route($lien['route']) }}"
                       @class([
                           'whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition',
                           'bg-edl-bleu text-white' => $active === $cle,
                           'text-gray-600 hover:bg-gray-100' => $active !== $cle,
                       ])>
                        {{ $lien['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0 flex-1 space-y-6">
            @if ($titre)
                <h1 class="text-2xl font-semibold text-edl-marron">{{ $titre }}</h1>
            @endif

            @if (session('succes'))
                <div class="rounded-md border border-edl-vert-fonce/30 bg-edl-vert-fonce/10 px-4 py-3 text-sm text-edl-vert-fonce">
                    {{ session('succes') }}
                </div>
            @endif

            @if (session('erreur'))
                <div class="rounded-md border border-edl-rose/30 bg-edl-rose/10 px-4 py-3 text-sm text-edl-rose">
                    {{ session('erreur') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
