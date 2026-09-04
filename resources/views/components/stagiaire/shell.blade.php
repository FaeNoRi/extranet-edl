@props(['active' => null])

@php
    $liens = [
        'dashboard'  => ['route' => 'stagiaire.dashboard',       'label' => 'Mon espace'],
        'ressources' => ['route' => 'stagiaire.ressources.index', 'label' => 'Ressources pédagogiques'],
    ];
@endphp

<div class="py-8">
    <div class="mx-auto flex max-w-5xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
        <aside class="lg:w-52 lg:flex-shrink-0">
            <nav class="flex gap-1 overflow-x-auto rounded-lg bg-white p-2 shadow-sm lg:flex-col">
                @foreach ($liens as $cle => $lien)
                    <a href="{{ route($lien['route']) }}"
                       @class([
                           'whitespace-nowrap rounded-md px-3 py-2 text-sm font-medium transition',
                           'bg-edl-rose text-white' => $active === $cle,
                           'text-gray-600 hover:bg-gray-100' => $active !== $cle,
                       ])>{{ $lien['label'] }}</a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0 flex-1 space-y-6">
            @if (session('succes'))
                <div class="rounded-md border border-edl-vert-fonce/30 bg-edl-vert-fonce/10 px-4 py-3 text-sm text-edl-vert-fonce">
                    {{ session('succes') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
