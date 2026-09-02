<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SessionFormation extends Model
{

    use HasFactory;

    protected $table = 'session_formations';

    protected $fillable = [
        'num_GESCOF', 'nom', 'code_produit', 'objectifs',
        'distanciel', 'lien_teams', 'client', 'dates_planning',
    ];

    protected $casts = ['distanciel' => 'boolean'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'session_formation_user');
    }

    public function seances(): BelongsToMany
    {
        return $this->belongsToMany(Seance::class, 'seances_session_formation');
    }
}
