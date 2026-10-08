<?php

namespace App\Models;

use App\Casts\SetCast;
use App\Models\Concerns\Journalisable;
use App\Support\NiveauxReferentiel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Referentiel extends Model
{
    use HasFactory, Journalisable;

    protected $table = 'referentiel';

    protected $fillable = ['module', 'langue', 'code', 'contenu', 'badge', 'niveaux'];

    protected function casts(): array
    {
        return [
            'niveaux' => SetCast::class,
        ];
    }

    /** Niveaux EDL affichables (« Essentiel, Consolidation » ou « tous niveaux »). */
    public function niveauxAffiches(): string
    {
        return NiveauxReferentiel::afficher($this->niveaux);
    }

    /** Langue affichable : nulle = entrée commune à toutes les langues. */
    public function langueAffichee(): string
    {
        return $this->langue ?? 'Toutes langues';
    }

    /** Entrées utilisables pour une langue de session : celles de cette langue + les communes. */
    public function scopePourLangue(Builder $query, ?string $langue): void
    {
        $query->where(fn ($q) => $q->whereNull('langue')->when($langue, fn ($q) => $q->orWhere('langue', $langue)));
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
