<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionJour extends Model
{
    use HasFactory;

    protected $table = 'session_jours';

    protected $fillable = ['session_formation_id', 'date', 'actif', 'commentaire'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'actif' => 'boolean',
        ];
    }

    public function sessionFormation(): BelongsTo
    {
        return $this->belongsTo(SessionFormation::class);
    }

    public function scopeActifs(Builder $query): void
    {
        $query->where('actif', true);
    }
}
