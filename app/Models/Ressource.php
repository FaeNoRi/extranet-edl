<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ressource extends Model
{

    use HasFactory;

    protected $table = 'ressources';

    protected $fillable = [
        'nom', 'type_fichier', 'chemin_fichier',
        'nom_fichier_original', 'nb_telechargement', 'uploader_id',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
