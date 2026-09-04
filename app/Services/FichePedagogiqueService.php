<?php

namespace App\Services;

use App\Models\Seance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Génère la fiche pédagogique PDF d'une séance et la range dans le dossier
 * de la séance. Visible uniquement du formateur et de l'admin, régénérée à
 * chaque enregistrement du formulaire.
 */
class FichePedagogiqueService
{
    public function generer(Seance $seance): string
    {
        $seance->loadMissing('sessionFormation.client', 'formateur', 'stagiaire', 'referentiels', 'ressources');

        $pdf = Pdf::loadView('pdf.fiche-pedagogique', ['seance' => $seance])
            ->setPaper('a4');

        $chemin = "seances/{$seance->id}/fiche-pedagogique-{$seance->date->format('Y-m-d')}.pdf";
        Storage::put($chemin, $pdf->output());

        $seance->forceFill(['fiche_pdf_path' => $chemin])->saveQuietly();

        return $chemin;
    }
}
