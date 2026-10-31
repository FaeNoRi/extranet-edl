{{-- Pastille colorée portant un pictogramme. `plein` : fond de la couleur (titres de carte) ; sinon fond
     teinté (menus, tuiles). Pictogramme toujours blanc sur fond plein (retour EDL) : décoratif et placé à
     côté de son libellé, il n'est pas soumis au contraste 3:1 des éléments graphiques. --}}
@props(['icone', 'couleur' => 'bleu', 'plein' => false, 'taille' => 'md'])

@php
    $teintes = [
        'bleu' => ['bg-edl-bleu/10 text-edl-bleu', 'bg-edl-bleu text-white'],
        'bleu-vert' => ['bg-edl-bleu-vert/15 text-edl-bleu-vert-fonce', 'bg-edl-bleu-vert text-white'],
        'vert' => ['bg-edl-vert/15 text-edl-vert-fonce', 'bg-edl-vert text-white'],
        'jaune' => ['bg-edl-jaune/15 text-edl-jaune-fonce', 'bg-edl-jaune text-white'],
        'orange' => ['bg-edl-orange/15 text-edl-orange-fonce', 'bg-edl-orange text-white'],
        'rose' => ['bg-edl-rose/10 text-edl-rose', 'bg-edl-rose text-white'],
        'violet' => ['bg-edl-violet/10 text-edl-violet', 'bg-edl-violet text-white'],
        'marron' => ['bg-edl-marron/10 text-edl-marron', 'bg-edl-marron text-white'],
    ];
    $tailles = [
        'sm' => ['h-8 w-8 rounded-lg', 'h-4 w-4'],
        'md' => ['h-9 w-9 rounded-lg', 'h-5 w-5'],
        'lg' => ['h-11 w-11 rounded-xl', 'h-6 w-6'],
    ];
@endphp

<span {{ $attributes->class(['inline-flex flex-none items-center justify-center', $tailles[$taille][0], $teintes[$couleur][$plein ? 1 : 0]]) }} aria-hidden="true">
    <x-icone :nom="$icone" :class="$tailles[$taille][1]" />
</span>
