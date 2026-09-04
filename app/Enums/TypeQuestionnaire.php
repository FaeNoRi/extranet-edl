<?php

namespace App\Enums;

enum TypeQuestionnaire: string
{
    case SatisfactionChaud = 'satisfaction_chaud';
    case SatisfactionFroid = 'satisfaction_froid';
    case EvaluationAcquis = 'evaluation_acquis';

    public function label(): string
    {
        return match ($this) {
            self::SatisfactionChaud => 'Satisfaction à chaud',
            self::SatisfactionFroid => 'Satisfaction à froid',
            self::EvaluationAcquis => 'Évaluation des acquis',
        };
    }
}
