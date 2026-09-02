<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identité de la structure
    |--------------------------------------------------------------------------
    |
    | Coordonnées et informations affichées dans le pied de page de toutes
    | les pages (exigence du cahier des charges, §2). À compléter avec les
    | valeurs réelles fournies par l'École des Langues Grand Calais.
    |
    */

    'structure' => [
        'nom' => 'École des Langues Grand Calais',
        'adresse' => env('EDL_ADRESSE', ''),
        'telephone' => env('EDL_TELEPHONE', ''),
        'email' => env('EDL_EMAIL', 'contact@edl-grandcalais.fr'),
    ],

    'horaires' => env('EDL_HORAIRES', 'Du lundi au vendredi, 9h00 – 17h00'),

    'liens' => [
        'site' => env('EDL_LIEN_SITE', 'https://www.edl-grandcalais.fr'),
        'facebook' => env('EDL_LIEN_FACEBOOK', 'https://www.facebook.com/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bandeau de certifications / financeurs
    |--------------------------------------------------------------------------
    |
    | Logos à afficher sur toutes les pages. Les fichiers seront déposés dans
    | public/img/partenaires/ ; en attendant, le pied de page affiche les
    | libellés.
    |
    */

    'certifications' => ['Qualiopi', 'CLOE'],

    'financeurs' => [
        // 'Région Hauts-de-France', 'France Travail', ...
    ],

];
