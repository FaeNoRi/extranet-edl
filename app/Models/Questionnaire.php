<?php

namespace App\Models;

use App\Enums\TypeQuestionnaire;
use App\Models\Concerns\Journalisable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    use HasFactory, Journalisable;

    protected $fillable = [
        'type', 'session_formation_id', 'titre', 'description', 'actif',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeQuestionnaire::class,
            'actif' => 'boolean',
        ];
    }

    public function sessionFormation(): BelongsTo
    {
        return $this->belongsTo(SessionFormation::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionnaireQuestion::class)->orderBy('ordre');
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(QuestionnaireReponse::class);
    }

    public function repondants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'questionnaire_soumissions')
            ->withPivot('soumis_at')
            ->withTimestamps();
    }

    public function estSoumisPar(User $user): bool
    {
        return $this->repondants()->whereKey($user->id)->exists();
    }

    /** Questionnaires visibles par un stagiaire : ceux de sa session ou communs. */
    public function scopePourSession(Builder $query, ?int $sessionId): void
    {
        $query->where('actif', true)
            ->where(fn ($q) => $q->where('session_formation_id', $sessionId)->orWhereNull('session_formation_id'));
    }
}
