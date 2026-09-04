<?php

namespace App\Models;

use App\Enums\Role;
use App\Models\Concerns\Journalisable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Journalisable, Notifiable, SoftDeletes;

    /** @var list<string> */
    protected array $journalAttributs = ['login', 'email', 'role', 'nom', 'prenom', 'formateur_fpc', 'formateur_op', 'deleted_at'];

    protected $fillable = [
        'email', 'login', 'password', 'role', 'nom', 'prenom',
        'photo_path', 'presentation', 'formateur_fpc', 'formateur_op',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
            'formateur_fpc' => 'boolean',
            'formateur_op' => 'boolean',
        ];
    }

    // --- Attributs -----------------------------------------------------------

    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

    // --- Rôles -------------------------------------------------------------

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isFormateur(): bool
    {
        return $this->role === Role::Formateur;
    }

    public function isStagiaire(): bool
    {
        return $this->role?->isStagiaire() ?? false;
    }

    public function isStagiaireFpc(): bool
    {
        return $this->role === Role::StagiaireFpc;
    }

    public function isStagiaireOp(): bool
    {
        return $this->role === Role::StagiaireOp;
    }

    public function scopeFormateurs(Builder $query): void
    {
        $query->where('role', Role::Formateur->value);
    }

    public function scopeStagiaires(Builder $query): void
    {
        $query->whereIn('role', [Role::StagiaireOp->value, Role::StagiaireFpc->value]);
    }

    // --- Relations ---------------------------------------------------------

    public function passwordResetTokens(): HasMany
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function sessionFormations(): BelongsToMany
    {
        return $this->belongsToMany(SessionFormation::class, 'session_formation_user')
            ->withPivot('disparu_import_at')
            ->withTimestamps();
    }

    /** Session du stagiaire (1 accès = 1 session). */
    public function sessionStagiaire(): ?SessionFormation
    {
        return $this->sessionFormations()->first();
    }

    /** Sessions dont le formateur est le référent. */
    public function sessionsEncadrees(): HasMany
    {
        return $this->hasMany(SessionFormation::class, 'formateur_id');
    }

    /** Sessions où le formateur intervient (co-animation). */
    public function sessionsCoAnimees(): BelongsToMany
    {
        return $this->belongsToMany(SessionFormation::class, 'session_formation_formateur')
            ->withPivot('principal')
            ->withTimestamps();
    }

    /**
     * Toutes les sessions encadrées (référent ou équipe).
     *
     * @return Collection<int, SessionFormation>
     */
    public function sessionsPourFormateur(): Collection
    {
        return $this->sessionsEncadrees()->get()
            ->concat($this->sessionsCoAnimees()->get())
            ->unique('id')
            ->values();
    }

    public function seancesAnimees(): HasMany
    {
        return $this->hasMany(Seance::class, 'formateur_id');
    }

    /** Fiches pédagogiques individuelles (stagiaire FPC). */
    public function fichesPedagogiques(): HasMany
    {
        return $this->hasMany(Seance::class, 'user_id');
    }

    public function emargements(): HasMany
    {
        return $this->hasMany(Emargement::class);
    }

    public function ressourcesUploadees(): HasMany
    {
        return $this->hasMany(Ressource::class, 'uploader_id');
    }

    public function referentiels(): BelongsToMany
    {
        return $this->belongsToMany(Referentiel::class, 'user_referentiel', 'user_id', 'referentiel_id')
            ->withPivot('consulte_at')
            ->withTimestamps();
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'user_documents');
    }

    public function questionnaireReponses(): HasMany
    {
        return $this->hasMany(QuestionnaireReponse::class);
    }
}
