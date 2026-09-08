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
        'forme_juridique' => env('EDL_FORME_JURIDIQUE', 'Association loi 1901'),
        'adresse' => env('EDL_ADRESSE', '3 rue Neuve, 62100 Calais'),
        'telephone' => env('EDL_TELEPHONE', '03 91 94 19 01'),
        'email' => env('EDL_EMAIL', 'contact@edl-grandcalais.com'),
        'siret' => env('EDL_SIRET', '830 076 238 00013'),
        'nda' => env('EDL_NDA', ''), // numéro de déclaration d'activité formation — à fournir
        'directeur_publication' => env('EDL_DIRECTEUR_PUBLICATION', ''), // à fournir
    ],

    /*
    |--------------------------------------------------------------------------
    | Mentions légales & RGPD
    |--------------------------------------------------------------------------
    */

    'legal' => [
        'hebergeur' => env('EDL_HEBERGEUR', 'OVH SAS — 2 rue Kellermann, 59100 Roubaix'),
        'dpo_contact' => env('EDL_DPO_CONTACT', ''),      // e-mail du délégué / référent RGPD — à fournir
        'conservation_op' => 'Comptes supprimés après la fermeture annuelle estivale.',
        'conservation_fpc' => 'Comptes supprimés au 31 décembre de l\'année suivant la fin de la formation.',
        'conservation_journal' => 'Journal des actions conservé 3 ans.',
    ],

    /*
    | Horaires d'ouverture : jour => plage (ou null si fermé).
    */
    'horaires' => [
        'Lundi' => '08h30 – 12h30 · 13h30 – 18h30',
        'Mardi' => '08h30 – 12h30 · 13h30 – 18h30',
        'Mercredi' => '08h30 – 12h30 · 13h30 – 18h30',
        'Jeudi' => '08h30 – 12h30 · 13h30 – 18h30',
        'Vendredi' => '08h30 – 12h30 · 13h30 – 18h30',
        'Samedi' => '08h30 – 12h30 · 13h30 – 16h30',
        'Dimanche' => null,
    ],

    'liens' => [
        'site' => env('EDL_LIEN_SITE', 'https://ecoledeslangues-grandcalais.com'),
        'facebook' => env('EDL_LIEN_FACEBOOK', 'https://www.facebook.com/ecoledeslanguesgrandcalais/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bandeau de certifications / financeurs
    |--------------------------------------------------------------------------
    |
    | Logos affichés sur toutes les pages (cahier des charges §2). Fichiers
    | dans public/img/partenaires/. Chaque logo est encadré sur fond blanc
    | pour respecter sa charte.
    |
    */

    'financeurs' => [
        ['nom' => 'Ville de Calais', 'logo' => 'img/partenaires/ville-de-calais.png', 'url' => 'https://www.calais.fr/'],
        ['nom' => 'Agglomération Grand Calais Terres et Mers', 'logo' => 'img/partenaires/grand-calais.png', 'url' => 'https://www.grandcalais.fr/'],
        ['nom' => 'Dispositif Cités Éducatives', 'logo' => 'img/partenaires/cites-educatives.png', 'url' => 'https://anct.gouv.fr/programmes-dispositifs/politique-de-la-ville/dispositifs/cites-educatives'],
        ['nom' => 'Engagement Quartiers 2030', 'logo' => 'img/partenaires/quartiers-2030.png', 'url' => 'https://www.pas-de-calais.gouv.fr/Actions-de-l-Etat/Cohesion-Sociale-Politique-de-la-Ville/Politique-de-la-Ville/La-politique-de-la-ville-dans-le-Pas-de-Calais/2024-2030-Contrats-de-Ville-engagements-2030'],
        ['nom' => 'Préfecture du Pas-de-Calais', 'logo' => 'img/partenaires/prefet-pas-de-calais.png', 'url' => 'https://www.pas-de-calais.gouv.fr/'],
    ],

    'certifications' => [
        ['nom' => 'Qualiopi', 'logo' => 'img/partenaires/qualiopi.png', 'url' => 'https://travail-emploi.gouv.fr/qualiopi-marque-de-certification-qualite-des-prestataires-de-formation'],
        ['nom' => 'CLOE', 'logo' => 'img/partenaires/cloe.png', 'url' => 'https://certifications-cloe.com/'],
    ],

    'logo' => 'img/logo-edl.png',
    'favicon' => 'favicon.png',

    /*
    |--------------------------------------------------------------------------
    | Purges de comptes (cahier des charges §1.2)
    |--------------------------------------------------------------------------
    |
    | - OP : suppression des comptes après la fermeture annuelle estivale.
    |   `op_apres` = jour à partir duquel la purge OP peut s'exécuter (MM-JJ) ;
    |   sont supprimés les comptes OP dont toutes les sessions se sont terminées
    |   avant le 1er septembre de l'année en cours.
    | - FPC : suppression au 31/12 de l'année N des comptes dont les formations
    |   se sont terminées en N-1 (déclenchement manuel par l'admin ; une tâche
    |   planifiée le propose aussi au 31/12).
    |
    */

    'purges' => [
        'op_apres' => env('EDL_PURGE_OP_APRES', '08-01'),
        'fpc_le' => env('EDL_PURGE_FPC_LE', '12-31'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Téléversements
    |--------------------------------------------------------------------------
    |
    | Extensions autorisées et taille maximale (Ko) pour les documents
    | administratifs et les ressources pédagogiques. Les fichiers exécutables
    | ou scripts sont refusés.
    |
    */

    'uploads' => [
        'extensions' => [
            'pdf', 'doc', 'docx', 'odt', 'rtf', 'txt',
            'ppt', 'pptx', 'odp', 'xls', 'xlsx', 'ods', 'csv',
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'mp3', 'wav', 'm4a', 'ogg',
            'mp4', 'webm', 'mov', 'avi',
            'zip',
        ],
        'taille_max_ko' => (int) env('EDL_UPLOAD_MAX_KO', 51200),
    ],

];
