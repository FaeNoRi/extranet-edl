<?php

namespace App\Services;

use App\Models\SessionFormation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

/**
 * Constitue l'archive ZIP d'une session FPC : un dossier par séance (nommé
 * d'après sa date) contenant la fiche pédagogique et les ressources
 * pédagogiques (cahier des charges : « pour enregistrement sur le Pcloud »).
 *
 * Un manifeste liste les fichiers attendus mais absents du stockage (fiches
 * PDF générées en phase 3, ressources téléversées ultérieurement).
 */
class SessionArchiveService
{
    public function __construct(private FichePedagogiqueService $fiches) {}

    /**
     * Toutes les fiches pédagogiques d'une session (FPC ou OP) dans un ZIP à plat, une par séance,
     * dans l'ordre chronologique : « AAAA-MM-JJ - stagiaire - fiche pedagogique.pdf ». Une fiche dont
     * le PDF manque (ou n'a jamais été généré) est régénérée à la volée.
     */
    public function telechargerFiches(SessionFormation $session): StreamedResponse
    {
        $session->load(['seances' => fn ($q) => $q->orderBy('date')->orderBy('id'), 'seances.stagiaire']);

        $chemin = tempnam(sys_get_temp_dir(), 'edlfiches').'.zip';
        $zip = new ZipArchive;
        if ($zip->open($chemin, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l\'archive.');
        }

        $noms = [];
        foreach ($session->seances as $seance) {
            if (! $seance->fiche_pdf_path || ! Storage::exists($seance->fiche_pdf_path)) {
                $this->fiches->generer($seance);
            }

            $base = $seance->date->format('Y-m-d')
                .($seance->stagiaire ? ' - '.Str::slug($seance->stagiaire->nom_complet) : '')
                .' - fiche pedagogique';
            // Deux séances le même jour (même stagiaire ou groupe OP) : suffixe (2), (3)…
            $noms[$base] = ($noms[$base] ?? 0) + 1;
            $nom = $base.($noms[$base] > 1 ? " ({$noms[$base]})" : '').'.pdf';

            $zip->addFromString($nom, Storage::get($seance->fiche_pdf_path));
        }
        $zip->close();

        $nom = 'fiches-pedagogiques-'.Str::slug($session->num_GESCOF).'-'.now()->format('Ymd').'.zip';

        return response()->streamDownload(function () use ($chemin) {
            readfile($chemin);
            @unlink($chemin);
        }, $nom, ['Content-Type' => 'application/zip']);
    }

    public function telecharger(SessionFormation $session): StreamedResponse
    {
        $chemin = $this->construire($session);
        $nom = 'session-'.Str::slug($session->num_GESCOF).'-'.now()->format('Ymd').'.zip';

        return response()->streamDownload(function () use ($chemin) {
            readfile($chemin);
            @unlink($chemin);
        }, $nom, ['Content-Type' => 'application/zip']);
    }

    private function construire(SessionFormation $session): string
    {
        $session->load([
            'seances' => fn ($q) => $q->orderBy('date'),
            'seances.ressources', 'seances.stagiaire',
        ]);

        $chemin = tempnam(sys_get_temp_dir(), 'edlzip').'.zip';
        $zip = new ZipArchive;

        if ($zip->open($chemin, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de créer l\'archive.');
        }

        $manifeste = ["Session {$session->num_GESCOF} — {$session->nom}", str_repeat('=', 40), ''];

        foreach ($session->seances as $seance) {
            $dossier = $seance->date->format('Y-m-d')
                .($seance->stagiaire ? ' - '.Str::slug($seance->stagiaire->nom_complet) : '');
            $zip->addEmptyDir($dossier);
            $rp = 1;

            // Fiche pédagogique.
            $ficheNom = "$dossier/fiche pedagogique - {$seance->date->format('d-m-Y')}.pdf";
            if ($seance->fiche_pdf_path && Storage::exists($seance->fiche_pdf_path)) {
                $zip->addFromString($ficheNom, Storage::get($seance->fiche_pdf_path));
            } else {
                $manifeste[] = "ABSENT : $ficheNom (fiche générée en phase 3)";
            }

            // Ressources pédagogiques.
            foreach ($seance->ressources as $ressource) {
                $ext = pathinfo($ressource->nom_fichier_original, PATHINFO_EXTENSION);
                $cible = "$dossier/{$seance->date->format('d-m-Y')}.RP{$rp}".($ext ? ".$ext" : '');
                if (Storage::exists($ressource->chemin_fichier)) {
                    $zip->addFromString($cible, Storage::get($ressource->chemin_fichier));
                } else {
                    $manifeste[] = "ABSENT : $cible  ({$ressource->nom})";
                }
                $rp++;
            }
        }

        $zip->addFromString('MANIFESTE.txt', implode("\n", $manifeste));
        $zip->close();

        return $chemin;
    }
}
