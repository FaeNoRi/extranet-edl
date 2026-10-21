{{-- Carte de contenu. Avec `icone`, l'en-tête prend la teinte de `couleur` et porte une pastille
     (refonte d'octobre 2026) ; sans, il reste blanc. --}}
@props(['titre' => null, 'actions' => null, 'icone' => null, 'couleur' => 'bleu'])

@php
    $entetes = [
        'bleu' => 'bg-edl-bleu/10 text-edl-bleu-fonce',
        'bleu-vert' => 'bg-edl-bleu-vert/15 text-edl-bleu-vert-fonce',
        'vert' => 'bg-edl-vert/15 text-edl-vert-fonce',
        'jaune' => 'bg-edl-jaune/15 text-edl-jaune-fonce',
        'orange' => 'bg-edl-orange/15 text-edl-orange-fonce',
        'rose' => 'bg-edl-rose/10 text-edl-rose-fonce',
        'violet' => 'bg-edl-violet/10 text-edl-violet-fonce',
        'marron' => 'bg-edl-marron/10 text-edl-marron',
    ];
@endphp

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-edl-sable bg-white']) }}>
    @if ($titre || $actions)
        <header @class([
            'flex items-center justify-between gap-3 px-5 py-3',
            $entetes[$couleur] => $icone,
            'border-b border-edl-sable text-edl-marron' => ! $icone,
        ])>
            <div class="flex items-center gap-3">
                @if ($icone)
                    <x-pastille :icone="$icone" :couleur="$couleur" plein />
                @endif
                <h2 class="font-semibold">{{ $titre }}</h2>
            </div>
            @if ($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endif
        </header>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>
</section>
