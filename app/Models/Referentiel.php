<?php

namespace App\Models;

use App\Casts\SetCast;
use App\Models\Concerns\Journalisable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Referentiel extends Model
{
    use HasFactory, Journalisable;

    protected $table = 'referentiel';

    protected $fillable = ['module', 'code', 'contenu', 'niveaux'];

    protected function casts(): array
    {
        return [
            'niveaux' => SetCast::class,
        ];
    }

    public function scopeModule(Builder $query, string $module): void
    {
        $query->where('module', $module);
    }

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(Ressource::class, 'referentiel_ressources', 'referentiel_id', 'ressource_id');
    }

    public function seances(): BelongsToMany
    {
        return $this->belongsToMany(Seance::class, 'seances_referentiel', 'referentiel_id', 'seance_id');
    }

    public function stagiaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_referentiel', 'referentiel_id', 'user_id')
            ->withPivot('consulte_at')
            ->withTimestamps();
    }
}
