<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Outils de normalisation des noms de personnes pour l'appariement à l'import.
 */
class Nom
{
    /** Clé de comparaison insensible à la casse, aux accents et à la ponctuation. */
    public static function cle(string $valeur): string
    {
        $valeur = SpreadsheetReader::sansAccents(mb_strtolower(trim($valeur)));
        $valeur = preg_replace('/[^a-z0-9]+/', ' ', $valeur) ?? '';

        return trim(preg_replace('/\s+/', ' ', $valeur) ?? '');
    }

    /**
     * Sépare une chaîne « Prénom NOM » (format des intervenants GESCOF) en
     * [prenom, nom] : les mots entièrement en majuscules constituent le nom.
     *
     * @return array{0: string, 1: string}
     */
    public static function separerPrenomNom(string $valeur): array
    {
        $mots = preg_split('/\s+/', trim($valeur)) ?: [];
        $prenom = [];
        $nom = [];

        foreach ($mots as $mot) {
            if ($mot !== '' && mb_strtoupper($mot) === $mot && preg_match('/\p{L}/u', $mot)) {
                $nom[] = $mot;
            } else {
                $prenom[] = $mot;
            }
        }

        // Repli : si aucun mot en majuscules, le dernier mot est le nom.
        if ($nom === [] && count($prenom) > 1) {
            $nom[] = array_pop($prenom);
        }

        return [
            Str::title(implode(' ', $prenom)),
            implode(' ', $nom),
        ];
    }
}
