<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Formateur = 'formateur';
    case StagiaireOp = 'stagiaire_op';
    case StagiaireFpc = 'stagiaire_fpc';

    /**
     * Libellé lisible du rôle.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Formateur => 'Formateur',
            self::StagiaireOp => 'Stagiaire OP',
            self::StagiaireFpc => 'Stagiaire FPC',
        };
    }

    /**
     * Nom de la route du tableau de bord correspondant au rôle.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Formateur => 'formateur.dashboard',
            self::StagiaireOp, self::StagiaireFpc => 'stagiaire.dashboard',
        };
    }

    public function isStagiaire(): bool
    {
        return in_array($this, [self::StagiaireOp, self::StagiaireFpc], true);
    }
}
