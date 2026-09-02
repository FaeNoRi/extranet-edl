<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email', 'login', 'password', 'role', 'nom', 'prenom',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * Nom complet « Prénom NOM ».
     */
    public function getNomCompletAttribute(): string
    {
        return trim($this->prenom.' '.$this->nom);
    }

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

    public function passwordResetTokens(): HasMany
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function sessionFormations(): BelongsToMany
    {
        return $this->belongsToMany(SessionFormation::class, 'session_formation_user');
    }

    public function referentiels(): BelongsToMany
    {
        return $this->belongsToMany(Referentiel::class, 'user_ressources', 'user_id', 'referentiel_id');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'user_documents');
    }

    public function ressourcesUploadees(): HasMany
    {
        return $this->hasMany(Ressource::class, 'uploader_id');
    }
}
