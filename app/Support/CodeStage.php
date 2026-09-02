<?php

namespace App\Support;

use App\Enums\CodeProduit;

/**
 * Analyse d'un code stage GESCOF (colonne « CodeProduit » de l'export).
 *
 * Exemples : AN-OP-8-9, MA-OP-6-10, ES-FPC-AIS, AN-CLSH, AN-ES-JI, RE-F,
 * AN-OP-ST, LSF-OP-Déb.
 *
 * Le code n'est pas normalisé : la langue est en préfixe, le type de produit
 * dans un segment, et « -ST » suffixe les stages (à exclure de l'import).
 */
readonly class CodeStage
{
    public function __construct(
        public string $brut,
        public ?string $langue,
        public ?CodeProduit $produit,
        public bool $estStage,
    ) {}

    public static function analyser(string $code): self
    {
        $code = trim($code);
        $segments = array_map('strtoupper', preg_split('/[-\s]+/', $code) ?: []);

        $langues = [
            'AN' => 'Anglais',
            'ES' => 'Espagnol',
            'MA' => 'Mandarin',
            'LSF' => 'Langue des signes française',
            'FR' => 'Français',
            'RE' => null, // « RE-F » : non déterminé
        ];

        $langue = $langues[$segments[0] ?? ''] ?? null;

        $estStage = in_array('ST', $segments, true);

        $produit = match (true) {
            in_array('FPC', $segments, true) => CodeProduit::Fpc,
            in_array('OP', $segments, true) => CodeProduit::Op,
            default => null, // CLSH, ES (journée d'immersion scolaire), RE-F, ...
        };

        return new self($code, $langue, $produit, $estStage);
    }

    /**
     * Le stagiaire relève-t-il du périmètre de la plateforme ?
     * (OP ou FPC, hors stages « -ST »).
     */
    public function eligiblePlateforme(): bool
    {
        return $this->produit !== null && ! $this->estStage;
    }

    public function raisonExclusion(): ?string
    {
        return match (true) {
            $this->estStage => 'stage ponctuel (-ST) exclu par le cahier des charges',
            $this->produit === null => 'hors périmètre plateforme (CLSH, immersion scolaire, autre)',
            default => null,
        };
    }
}
