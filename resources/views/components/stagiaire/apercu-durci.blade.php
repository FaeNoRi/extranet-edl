@props(['url', 'type', 'titre' => null])

{{--
    Lecteur « durci » : dissuasion standard contre la capture (pas de bouton
    téléchargement, clic droit désactivé, filigrane au nom de la structure).
    Aucune solution web ne bloque réellement une capture d'écran ; c'est un
    choix assumé côté client.
--}}
<div class="relative overflow-hidden rounded-lg bg-white shadow-sm" style="height: 82vh; min-height: 560px;"
     oncontextmenu="return false" onselectstart="return false">
    @if ($type === 'video' || $type === 'audio')
        <video src="{{ $url }}" controls controlsList="nodownload noremoteplayback" disablepictureinpicture
               oncontextmenu="return false" class="h-full w-full bg-black"></video>
    @else
        <iframe src="{{ $url }}#toolbar=0&navpanes=0" class="h-full w-full" title="{{ $titre ?? 'Aperçu du document' }}"></iframe>
    @endif

    <x-stagiaire.filigrane />
</div>
