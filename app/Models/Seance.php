<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Seance extends Model
{
    use HasFactory;

    protected $table = 'seances';

    protected $fillable = ['date', 'description', 'outils', 'analyse_seance'];

    protected $casts = ['date' => 'date'];

    public function ressources(): BelongsToMany
    {
        return $this->belongsToMany(Ressource::class, 'seances_ressources', 'seance_id', 'ressource_id');
    }

    public function referentiels(): BelongsToMany
    {
        return $this->belongsToMany(Referentiel::class, 'seances_referentiel', 'seance_id', 'referentiel_id');
    }

    public function sessionFormations(): BelongsToMany
    {
        return $this->belongsToMany(SessionFormation::class, 'seances_session_formation');
    }
}
