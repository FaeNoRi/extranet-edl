<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ressource extends Model
{
    use HasFactory;

    protected $table = 'ressources';

    protected $fillable = [
        'nom', 'type_fichier', 'chemin_fichier', 'nom_fichier_original',
        'taille', 'nb_telechargement', 'uploader_id', 'session_formation_id',
    ];

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
            'nb_telechargement' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function sessionFormation(): BelongsTo
    {
        return $this->belongsTo(SessionFormation::class);
    }

    public function seances(): BelongsToMany
    {
        return $this->belongsToMany(Seance::class, 'seances_ressources', 'ressource_id', 'seance_id')
            ->withPivot('transmis');
    }

    public function referentiels(): BelongsToMany
    {
        return $this->belongsToMany(Referentiel::class, 'referentiel_ressources', 'ressource_id', 'referentiel_id');
    }
}
