<?php

namespace App\Enums;

enum CodeProduit: string
{
    case Fpc = 'FPC';
    case Op = 'OP';

    public function label(): string
    {
        return match ($this) {
            self::Fpc => 'Formation Professionnelle Continue',
            self::Op => 'Objectif Perfectionnement',
        };
    }
}
