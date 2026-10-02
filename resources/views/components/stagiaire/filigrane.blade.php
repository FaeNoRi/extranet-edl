@props(['nombre' => 24, 'couleur' => 'text-edl-marron'])

{{-- Filigrane de marque : toujours le nom de la structure, quel que soit le lecteur. --}}
<div class="pointer-events-none absolute inset-0 z-10 flex select-none flex-wrap content-around justify-around overflow-hidden opacity-10" aria-hidden="true">
    @for ($i = 0; $i < $nombre; $i++)
        <span class="rotate-[-25deg] whitespace-nowrap text-sm font-semibold {{ $couleur }}">{{ config('edl.structure.nom') }}</span>
    @endfor
</div>
