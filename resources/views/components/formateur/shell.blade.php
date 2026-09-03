@props(['active' => null, 'titre' => null])

@php
    $liens = [
        'dashboard' => ['route' => 'formateur.dashboard',     'label' => 'Vue d\'ensemble'],
        'sessions'  => ['route' => 'formateur.sessions.index', 'label' => 'Mes sessions'],
    ];
@endphp

<div class="py-8">
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <aside class="lg:w-52 lg:flex-shrink-0">
            <nav class="flex gap-1 overflow-x-auto rounded-lg bg-white p-2 shadow-sm lg:flex-col">
                @foreach ($liens as $cle => $lien)
                    <a href="{{ route($lien['route']) }}"
                       @class([
                           'whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition',
                           'bg-edl-orange text-white' => $active === $cle,
                           'text-gray-600 hover:bg-gray-100' => $active !== $cle,
                       ])>{{ $lien['label'] }}</a>
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

            @if ($errors->any())
                <div class="rounded-md border border-edl-rose/30 bg-edl-rose/10 px-4 py-3 text-sm text-edl-rose">
                    <p class="font-medium">Le formulaire contient des erreurs :</p>
                    <ul class="mt-1 list-disc pl-5">
                        @foreach ($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
