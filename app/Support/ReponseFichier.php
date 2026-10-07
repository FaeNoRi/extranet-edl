<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Réponse « à l'écran » (inline) pour un fichier déposé par un utilisateur.
 *
 * Le type MIME servi est celui détecté dans le CONTENU du fichier, pas celui de son extension :
 * un fichier HTML renommé en .jpg serait sinon affiché comme une page de l'extranet (script
 * exécuté avec les droits de la personne connectée). On refuse donc tout ce qui n'est pas un
 * PDF, une image, un son ou une vidéo. Hors PDF, la réponse est en plus enfermée dans un bac à
 * sable (pas pour les PDF : le visualiseur des navigateurs refuse de s'afficher dans un document
 * sandboxé, ce qui casserait le volet de lecture des stagiaires FPC).
 */
class ReponseFichier
{
    public static function enLigne(string $chemin): StreamedResponse
    {
        $mime = (string) Storage::mimeType($chemin);
        abort_unless(self::affichable($mime), 415, 'Ce type de fichier ne peut pas être affiché à l\'écran.');

        $reponse = Storage::response($chemin);
        $reponse->headers->set('X-Content-Type-Options', 'nosniff');

        if ($mime !== 'application/pdf') {
            $reponse->headers->set('Content-Security-Policy', "default-src 'none'; sandbox");
        }

        return $reponse;
    }

    public static function affichable(string $mime): bool
    {
        return $mime === 'application/pdf'
            || $mime === 'application/ogg'
            || (bool) preg_match('#^(image/(jpeg|png|gif|webp)|audio/[\w.+-]+|video/[\w.+-]+)$#', $mime);
    }
}
