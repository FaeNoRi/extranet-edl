<?php

namespace App\Models;

use App\Models\Concerns\Journalisable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seance extends Model
{
    use HasFactory, Journalisable;

    protected $table = 'seances';

    protected $fillable = [
        'session_formation_id', 'formateur_id', 'user_id', 'date', 'langue',
        'objectifs', 'contenu', 'outils', 'sources', 'analyse_seance', 'fiche_pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'objectifs' => 'array',
            'outils' => 'array',
        ];
    }

    /** Nom du dossier de la séance (cf. cahier des charges). */
    public function getNomDossierAttribute(): string
    {
        return $this->date?->format('d/m/Y') ?? '';
    }

    // --- Relations ---------------------------------------------------------

    public function sessionFormation(): BelongsTo
    {
        return $this->belongsTo(SessionFormation::class);
    }

    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    /** Stagiaire concerné pour une fiche pédagogique FPC individuelle. */
    public function stagiaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referentiels(): BelongsToMany
    {
        return $this->belongsToMany(Referentiel::class, 'seances_referentiel', 'seance_id', 'referentiel_id');
    }

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(Ressource::class, 'seances_ressources', 'seance_id', 'ressource_id')
            ->withPivot('transmis');
    }

    /** Ressources visibles par le stagiaire. */
    public function ressourcesTransmises(): BelongsToMany
    {
        return $this->ressources()->wherePivot('transmis', true);
    }

    public function emargements(): HasMany
    {
        return $this->hasMany(Emargement::class);
    }
}
