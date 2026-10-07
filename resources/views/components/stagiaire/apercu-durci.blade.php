@props(['url', 'type', 'titre' => null])

{{--
    Lecteur « durci » (stagiaires OP) : dissuasion contre la récupération des
    documents. Le PDF n'est pas confié au lecteur natif du navigateur (dont la
    barre d'outils propose enregistrement et impression) mais dessiné par
    PDF.js sur des canvas, avec le filigrane incrusté dans l'image. Aucune
    solution web n'empêche une capture d'écran ; ce sont des freins assumés.
--}}
@once
    @vite('resources/js/lecteur-pdf.js')
@endonce

<div data-lecteur-durci class="relative overflow-hidden rounded-lg bg-white shadow-sm print:hidden"
     style="height: 82vh; min-height: 560px;">
    @if ($type === 'pdf')
        <div data-lecteur-pdf data-url="{{ $url }}" data-filigrane="{{ config('edl.structure.nom') }}"
             class="flex h-full flex-col">
            <div class="flex items-center gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                <button type="button" data-action="precedent" aria-label="Page précédente"
                        class="rounded px-2 py-1 hover:bg-gray-200">‹</button>
                <span data-page-info class="min-w-[4.5rem] text-center tabular-nums">– / –</span>
                <button type="button" data-action="suivant" aria-label="Page suivante"
                        class="rounded px-2 py-1 hover:bg-gray-200">›</button>
                <span class="mx-2 h-4 w-px bg-gray-300"></span>
                <button type="button" data-action="zoom-moins" aria-label="Réduire le zoom"
                        class="rounded px-2 py-1 hover:bg-gray-200">−</button>
                <button type="button" data-action="zoom-plus" aria-label="Augmenter le zoom"
                        class="rounded px-2 py-1 hover:bg-gray-200">+</button>
                <button type="button" data-action="ajuster"
                        class="rounded px-2 py-1 hover:bg-gray-200">Ajuster à la largeur</button>
            </div>
            <div data-zone role="region" tabindex="0" aria-label="Pages du document : flèches haut et bas pour défiler" class="relative flex-1 overflow-y-auto bg-gray-200 p-4">
                <p data-etat class="py-10 text-center text-sm text-gray-500">Chargement du document…</p>
            </div>
        </div>
    @elseif ($type === 'video' || $type === 'audio')
        <video src="{{ $url }}" controls controlsList="nodownload noremoteplayback" disablepictureinpicture
               class="h-full w-full bg-black"></video>
        <x-stagiaire.filigrane />
    @elseif ($type === 'image')
        <img src="{{ $url }}" alt="{{ $titre }}" draggable="false" class="h-full w-full select-none object-contain">
        <x-stagiaire.filigrane />
    @else
        <div class="flex h-full items-center justify-center p-6 text-center text-sm text-gray-500">
            Ce type de fichier ne peut pas être consulté en ligne.
        </div>
    @endif
</div>

<p class="hidden text-center text-sm text-gray-500 print:block">Impression non autorisée.</p>
