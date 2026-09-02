<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        return ['email_verified_at' => 'datetime'];
    }

    public function sessionFormations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(SessionFormation::class, 'session_formation_user');
    }

    public function referentiels(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Referentiel::class, 'user_ressources', 'user_id', 'referentiel_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'user_documents');
    }

    public function ressourcesUploadees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ressource::class, 'uploader_id');
    }
}
