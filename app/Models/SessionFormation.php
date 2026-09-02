<?php

namespace App\Models;

use App\Enums\CodeProduit;
use App\Models\Concerns\Journalisable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessionFormation extends Model
{
    use HasFactory, Journalisable;

    protected $table = 'session_formations';

    protected $fillable = [
        'num_GESCOF', 'nom', 'code_stage', 'code_produit', 'langue', 'client_id',
        'formateur_id', 'intervenants_import', 'objectifs', 'distanciel', 'lien_teams',
        'rythme_op', 'dates_planning', 'gescof_importe_at',
    ];

    protected function casts(): array
    {
        return [
            'code_produit' => CodeProduit::class,
            'distanciel' => 'boolean',
            'gescof_importe_at' => 'datetime',
        ];
    }

    public function isFpc(): bool
    {
        return $this->code_produit === CodeProduit::Fpc;
    }

    public function isOp(): bool
    {
        return $this->code_produit === CodeProduit::Op;
    }

    // --- Relations ---------------------------------------------------------

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Formateur référent. */
    public function formateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'formateur_id');
    }

    /** Équipe pédagogique (co-animation). */
    public function formateurs(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'session_formation_formateur')
            ->withPivot('principal')
            ->withTimestamps();
    }

    public function stagiaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'session_formation_user')
            ->withPivot('disparu_import_at')
            ->withTimestamps();
    }

    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class);
    }

    public function jours(): HasMany
    {
        return $this->hasMany(SessionJour::class);
    }

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressource::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function questionnaires(): HasMany
    {
        return $this->hasMany(Questionnaire::class);
    }
}
