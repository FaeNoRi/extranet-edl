<?php

namespace App\Models;

use App\Casts\SetCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Referentiel extends Model
{
    use HasFactory;

    protected $table = 'referentiel';

    protected $fillable = ['module', 'code', 'contenu', 'niveaux'];

    protected function casts(): array
    {
        return [
            'niveaux' => SetCast::class,
        ];
    }

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(Ressource::class, 'referentiel_ressources', 'referentiel_id', 'ressources_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_ressources', 'referentiel_id', 'user_id');
    }
}
