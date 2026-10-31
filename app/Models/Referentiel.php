<?php

namespace App\Models;

use App\Casts\SetCast;
use App\Models\Concerns\Journalisable;
use App\Support\NiveauxReferentiel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Referentiel extends Model
{
    use HasFactory, Journalisable;

    protected $table = 'referentiel';

    /** Modules de la trame v3, dans l'ordre d'affichage (les entrées archivées peuvent en porter d'autres). */
    public const MODULES = [
        'Bases', 'Conjugaison', 'Grammaire', 'Construction de phrases', 'Prononciation',
        'Compétences', 'Vocabulaire', 'Préparation au test CLOE',
    ];

    protected $fillable = ['module', 'langue', 'code', 'contenu', 'badges', 'niveaux', 'archive_le'];

    protected function casts(): array
    {
        return [
            'niveaux' => SetCast::class,
            'badges' => 'array',
            'archive_le' => 'datetime',
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

    public function estArchivee(): bool
    {
        return $this->archive_le !== null;
    }

    /** Entrées encore proposées aux formateurs (hors trames précédentes archivées). */
    public function scopeActives(Builder $query): void
    {
        $query->whereNull('archive_le');
    }

    /** Badges normalisés à partir d'une saisie « COM, rh , ORAL » : majuscules, sans doublon. */
    public static function normaliserBadges(?string $saisie): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($b) => mb_strtoupper(trim($b)), explode(',', (string) $saisie)
        ))));
    }

    /**
     * Regroupe des entrées par module, dans l'ordre de la trame (modules inconnus à la fin).
     *
     * @param  Collection<int, self>  $entrees
     */
    public static function grouperParModule(Collection $entrees): Collection
    {
        return $entrees->groupBy('module')
            ->sortBy(fn ($groupe, $module) => array_search($module, self::MODULES, true) === false ? 999 : array_search($module, self::MODULES, true));
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
