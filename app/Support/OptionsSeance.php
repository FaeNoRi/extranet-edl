<?php

namespace App\Support;

use App\Models\SessionFormation;

/**
 * Listes de choix du formulaire de fiche pédagogique (cahier des charges p. 4).
 */
class OptionsSeance
{
    /** Objectifs pédagogiques standard (cases à cocher). */
    public const OBJECTIFS = [
        'Identifier et utiliser un vocabulaire adapté au contexte',
        'Utiliser les principales structures grammaticales nécessaires pour construire des phrases cohérentes et de complexité variée',
        'Comprendre un court texte et en dégager le sens global et des détails spécifiques',
        "Comprendre un énoncé, des instructions ou les questions d'un interlocuteur",
        'Communiquer en situation socioprofessionnelle, professionnelle ou spécifique à son activité en employant les expressions adaptées',
        'Tenir une discussion sur un sujet professionnel',
        "Communiquer de façon naturelle et développer l'interaction orale",
        'Se faire comprendre avec une prononciation claire',
    ];

    /** Types d'outils et de supports utilisés durant la séance. */
    public const OUTILS = [
        'Livres', 'Magazines', 'Vidéos', 'Sites internet', 'Jeux', 'Autre',
    ];

    /**
     * Objectifs proposés pour une session : liste standard + objectifs
     * personnalisés saisis par l'admin pour les sessions FPC.
     *
     * @return list<string>
     */
    public static function objectifsPour(SessionFormation $session): array
    {
        $perso = collect(preg_split('/\r\n|\r|\n/', (string) $session->objectifs))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all();

        return array_values(array_unique([...self::OBJECTIFS, ...$perso]));
    }
}
