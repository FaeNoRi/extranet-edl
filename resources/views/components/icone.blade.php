{{-- Pictogramme décoratif (trait, hérite de la couleur du texte). Toujours masqué aux lecteurs d'écran :
     le libellé voisin porte le sens. --}}
@props(['nom'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($nom)
        @case('accueil')<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>@break
        @case('dossier')<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>@break
        @case('questionnaire')<path d="M9 4h6v3H9z"/><path d="M9 5H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-3"/><path d="m9 14 2 2 4-4"/>@break
        @case('calendrier')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>@break
        @case('groupe')<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14a6 6 0 0 1 3.5 6"/>@break
        @case('personne')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>@break
        @case('import')<path d="M12 15V3M7 8l5-5 5 5"/><path d="M4 15v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>@break
        @case('document')<path d="M14 3H6a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>@break
        @case('globe')<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>@break
        @case('horloge')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
        @case('coche')<path d="M20 6 9 17l-5-5"/>@break
        @case('crayon')<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>@break
        @case('video')<rect x="2" y="6" width="14" height="12" rx="2"/><path d="m16 10 6-3v10l-6-3"/>@break
        @case('livre')<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5z"/><path d="M4 19.5V4.5M8 7h8"/>@break
        @case('corbeille')<path d="M3 6h18M8 6V4h8v2M6 6l1 15h10l1-15"/>@break
        @case('journal')<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>@break
        @case('bouclier')<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/>@break
        @case('alerte')<path d="M12 3 2 20h20z"/><path d="M12 10v4M12 17h.01"/>@break
        @case('graphique')<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>@break
        @case('chevron')<path d="m9 18 6-6-6-6"/>@break
        @case('fleche')<path d="M5 12h14M13 6l6 6-6 6"/>@break
    @endswitch
</svg>
