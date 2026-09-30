@props(['url', 'type', 'titre' => null])

{{--
    Lecteur « durci » : dissuasion standard contre la capture (pas de bouton
    téléchargement, clic droit désactivé, filigrane nom + login). Aucune
    solution web ne bloque réellement une capture d'écran ; c'est un choix
    assumé côté client.
--}}
<div class="relative overflow-hidden rounded-lg bg-white shadow-sm" style="height: 70vh;"
     oncontextmenu="return false" onselectstart="return false">
    @if ($type === 'video' || $type === 'audio')
        <video src="{{ $url }}" controls controlsList="nodownload noremoteplayback" disablepictureinpicture
               oncontextmenu="return false" class="h-full w-full bg-black"></video>
    @else
        <iframe src="{{ $url }}#toolbar=0&navpanes=0" class="h-full w-full" title="{{ $titre ?? 'Aperçu du document' }}"></iframe>
    @endif

    <div class="pointer-events-none absolute inset-0 z-10 flex select-none flex-wrap content-around justify-around overflow-hidden opacity-10">
        @for ($i = 0; $i < 18; $i++)
            <span class="rotate-[-25deg] whitespace-nowrap text-sm font-semibold text-edl-marron">
                {{ auth()->user()->nom_complet }} · {{ auth()->user()->login }}
            </span>
        @endfor
    </div>
</div>
