<?php

namespace App\Enums;

enum TypeQuestion: string
{
    case Texte = 'texte';
    case ChoixUnique = 'choix_unique';
    case ChoixMultiple = 'choix_multiple';
    case Echelle = 'echelle';

    public function label(): string
    {
        return match ($this) {
            self::Texte => 'Réponse libre',
            self::ChoixUnique => 'Choix unique',
            self::ChoixMultiple => 'Choix multiple',
            self::Echelle => 'Échelle de 1 à 5',
        };
    }

    public function attendOptions(): bool
    {
        return in_array($this, [self::ChoixUnique, self::ChoixMultiple], true);
    }
}
