{{-- Menu latéral des espaces : chaque rubrique a son pictogramme dans une pastille de sa couleur ;
     la rubrique active prend la couleur de l'espace. --}}
@props(['liens', 'active' => null, 'espace' => 'admin', 'label'])

@php
    $actif = [
        'admin' => 'bg-edl-bleu text-white',
        'formateur' => 'bg-edl-orange text-gray-900',
        'stagiaire' => 'bg-edl-rose text-white',
    ][$espace];
@endphp

<nav aria-label="{{ $label }}" class="flex gap-1 overflow-x-auto rounded-xl border border-edl-sable bg-white p-2 lg:flex-col lg:overflow-visible">
    @foreach ($liens as $cle => $lien)
        <a href="{{ route($lien['route']) }}"
           @if ($active === $cle) aria-current="page" @endif
           @class([
               'flex items-center gap-3 whitespace-nowrap rounded-lg px-2.5 py-1.5 text-sm font-medium transition',
               $actif => $active === $cle,
               'text-edl-marron hover:bg-edl-fond' => $active !== $cle,
           ])>
            @if ($active === $cle)
                <span class="inline-flex h-8 w-8 flex-none items-center justify-center" aria-hidden="true">
                    <x-icone :nom="$lien['icone']" />
                </span>
            @else
                <x-pastille :icone="$lien['icone']" :couleur="$lien['couleur']" taille="sm" />
            @endif
            <span>{{ $lien['label'] }}</span>
        </a>
    @endforeach
</nav>
