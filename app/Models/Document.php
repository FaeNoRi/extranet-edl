<?php

namespace App\Models;

use App\Models\Concerns\Journalisable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Document extends Model
{
    use HasFactory, Journalisable;

    protected $fillable = [
        'nom', 'categorie', 'type_document', 'session_formation_id',
        'chemin_fichier', 'nom_fichier_original', 'taille', 'uploader_id',
    ];

    protected function casts(): array
    {
        return ['taille' => 'integer'];
    }

    public function sessionFormation(): BelongsTo
    {
        return $this->belongsTo(SessionFormation::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function stagiaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_documents');
    }

    /** Documents communs à toute la structure. */
    public function scopeStructure(Builder $query): void
    {
        $query->whereNull('session_formation_id');
    }

    public function scopeCategorie(Builder $query, string $categorie): void
    {
        $query->where('categorie', $categorie);
    }
}
