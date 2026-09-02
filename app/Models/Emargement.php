<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Emargement extends Model
{
    use HasFactory;

    protected $fillable = [
        'seance_id', 'user_id', 'present', 'signe_at', 'signature_path', 'commentaire',
    ];

    protected function casts(): array
    {
        return [
            'present' => 'boolean',
            'signe_at' => 'datetime',
        ];
    }

    public function seance(): BelongsTo
    {
        return $this->belongsTo(Seance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
