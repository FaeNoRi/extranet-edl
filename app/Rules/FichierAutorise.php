<?php

namespace App\Rules;

/**
 * Règles de validation partagées pour les téléversements (documents et
 * ressources pédagogiques) : extensions autorisées + taille maximale.
 */
class FichierAutorise
{
    /**
     * @return array<int, string>
     */
    public static function regles(bool $obligatoire = true): array
    {
        return array_filter([
            $obligatoire ? 'required' : 'nullable',
            'file',
            'extensions:'.implode(',', config('edl.uploads.extensions')),
            'max:'.config('edl.uploads.taille_max_ko'),
        ]);
    }
}
