<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Journalise automatiquement les créations, modifications et suppressions
 * du modèle (exigence CDC : « journal des actions — qui a modifié quoi »
 * avec accès aux valeurs antérieures).
 *
 * Les modèles peuvent surcharger $journalAttributs pour restreindre les
 * champs suivis.
 */
trait Journalisable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->journalAttributs ?? ['*'])
            ->logExcept(['password', 'remember_token', 'updated_at', 'created_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName(class_basename($this));
    }
}
