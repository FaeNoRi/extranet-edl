<?php

namespace App\Services;

use App\Models\GescofImport;
use Illuminate\Support\Facades\Storage;

/**
 * Supprime les fichiers GESCOF téléversés puis abandonnés (simulation jamais
 * appliquée, import interrompu) : ils contiennent des noms et des adresses
 * e-mail de stagiaires et ne doivent pas rester stockés sans limite. Un
 * fichier appliqué est déjà supprimé par l'import lui-même.
 */
class PurgeImportsGescofService
{
    /** Retourne le nombre de fichiers supprimés. */
    public function purger(int $jours): int
    {
        $limite = now()->subDays($jours)->getTimestamp();
        $supprimes = 0;

        foreach (Storage::files('gescof') as $chemin) {
            if (Storage::lastModified($chemin) >= $limite) {
                continue;
            }

            Storage::delete($chemin);

            // La simulation reste consultable, mais ne peut plus être appliquée.
            GescofImport::where('fichier_path', $chemin)->update(['fichier_path' => null]);

            $supprimes++;
        }

        return $supprimes;
    }
}
