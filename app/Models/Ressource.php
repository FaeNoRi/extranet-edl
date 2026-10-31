<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\UploadedFile;

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

    /** Type affiché/lu par les lecteurs, déduit du fichier téléversé. */
    public static function typeDepuisFichier(UploadedFile $fichier): string
    {
        $mime = (string) $fichier->getMimeType();

        return match (true) {
            str_starts_with($mime, 'audio/') => 'audio',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'image/') => 'image',
            strtolower($fichier->getClientOriginalExtension()) === 'pdf' => 'pdf',
            default => 'autre',
        };
    }

    public function referentiels(): BelongsToMany
    {
        return $this->belongsToMany(Referentiel::class, 'referentiel_ressources', 'ressource_id', 'referentiel_id');
    }
}
