<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Photo d'un formateur, servie par l'application plutôt que par le lien public/storage : ce lien
 * symbolique ne peut pas être créé sur un hébergement mutualisé sans SSH (dépôt par FTP), et la
 * photo n'est ainsi visible que des utilisateurs connectés.
 */
class PhotoFormateurController extends Controller
{
    public function __invoke(User $formateur): StreamedResponse
    {
        abort_unless($formateur->isFormateur() && $formateur->photo_path, 404);
        abort_unless(Storage::disk('public')->exists($formateur->photo_path), 404);

        return Storage::disk('public')->response($formateur->photo_path, null, [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
