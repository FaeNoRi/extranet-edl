<?php

namespace App\Support;

/**
 * Niveaux du référentiel EDL (remplacent les niveaux CECRL A1…C2 depuis octobre 2026).
 * La clé est stockée en base, le libellé est affiché ; l'équivalence CECRL sert de repère.
 */
class NiveauxReferentiel
{
    public const LIBELLES = [
        'essentiel' => 'Essentiel',
        'consolidation' => 'Consolidation',
        'perfectionnement' => 'Perfectionnement',
    ];

    public const EQUIVALENCES = [
        'essentiel' => 'A1/A2',
        'consolidation' => 'B1/B2',
        'perfectionnement' => 'C1/C2',
    ];

    /** @return array<int, string> */
    public static function cles(): array
    {
        return array_keys(self::LIBELLES);
    }

    /** « Essentiel (A1/A2) » */
    public static function libelleComplet(string $cle): string
    {
        return (self::LIBELLES[$cle] ?? $cle).' ('.(self::EQUIVALENCES[$cle] ?? '?').')';
    }

    /**
     * Libellés d'une liste de clés, dans l'ordre de progression ; « tous niveaux » si vide.
     *
     * @param  array<int, string>  $cles
     */
    public static function afficher(array $cles): string
    {
        $tries = array_values(array_intersect(self::cles(), $cles));

        return $tries === [] ? 'tous niveaux' : implode(', ', array_map(fn ($c) => self::LIBELLES[$c], $tries));
    }

    /**
     * Conversion des anciens niveaux CECRL (A1, B2…) vers les niveaux EDL, sans doublon.
     *
     * @param  array<int, string>  $cecrl
     * @return array<int, string>
     */
    public static function depuisCecrl(array $cecrl): array
    {
        $correspondance = ['A' => 'essentiel', 'B' => 'consolidation', 'C' => 'perfectionnement'];
        $cles = [];
        foreach ($cecrl as $niveau) {
            $niveau = strtolower(trim($niveau));
            if (isset(self::LIBELLES[$niveau])) {
                $cles[] = $niveau; // déjà converti
            } elseif (isset($correspondance[strtoupper($niveau[0] ?? '')])) {
                $cles[] = $correspondance[strtoupper($niveau[0])];
            }
        }

        return array_values(array_intersect(self::cles(), $cles));
    }
}
