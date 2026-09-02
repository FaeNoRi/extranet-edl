<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GescofImport extends Model
{
    protected $fillable = [
        'user_id', 'fichier_nom', 'fichier_path', 'applique',
        'lignes_lues', 'lignes_ignorees', 'comptes_crees', 'comptes_reactives',
        'comptes_disparus', 'sessions_creees', 'sessions_maj', 'anomalies',
    ];

    protected function casts(): array
    {
        return [
            'applique' => 'boolean',
            'anomalies' => 'array',
        ];
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
