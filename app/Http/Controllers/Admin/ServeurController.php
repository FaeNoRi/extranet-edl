<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PreflightService;
use App\Services\TachesPlanifieesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * État du serveur et mise à jour de la base depuis l'administration : sur l'hébergement mutualisé
 * sans SSH, ni `edl:preflight` ni `migrate` ne peuvent être lancés en ligne de commande.
 */
class ServeurController extends Controller
{
    public function index(Request $request, PreflightService $preflight, TachesPlanifieesService $taches): View
    {
        return view('admin.serveur.index', [
            'controles' => $preflight->controles(),
            'migrations' => $this->migrationsEnAttente($preflight),
            'temoin' => Cache::get(TachesPlanifieesService::TEMOIN),
            'rapport' => $taches->dernierRapport(),
            'sauvegardes' => $this->sauvegardes(),
            'requete' => [
                'Adresse du visiteur vue par l\'application' => $request->ip(),
                'Adresse de connexion (REMOTE_ADDR)' => $request->server('REMOTE_ADDR'),
                'En-tête X-Forwarded-For' => $request->header('X-Forwarded-For') ?: '—',
                'En-tête X-Forwarded-Proto' => $request->header('X-Forwarded-Proto') ?: '—',
                'Connexion vue comme HTTPS' => $request->isSecure() ? 'oui' : 'non',
            ],
            'php' => [
                'Dossier de l\'application (chemin du .htpasswd)' => base_path(),
                'Version de PHP' => PHP_VERSION,
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time').' s',
                'Fonctions désactivées' => ini_get('disable_functions') ?: '—',
            ],
        ]);
    }

    public function migrer(Request $request, PreflightService $preflight): RedirectResponse
    {
        $attente = $this->migrationsEnAttente($preflight);

        if ($attente === []) {
            return back()->with('succes', 'La base est déjà à jour.');
        }

        if ($request->boolean('sauvegarder')) {
            try {
                $ok = Artisan::call('backup:run', ['--only-db' => true, '--disable-notifications' => true]) === 0;
                $sortie = Artisan::output();
            } catch (Throwable $e) {
                [$ok, $sortie] = [false, $e->getMessage()];
            }

            if (! $ok) {
                return back()->with('erreur', 'La sauvegarde préalable a échoué, la base n\'a pas été modifiée : '
                    .mb_substr(trim($sortie), -500).' — Décochez « Sauvegarder la base avant » seulement si une autre sauvegarde récente existe (sauvegardes automatiques OVH).');
            }
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $e) {
            report($e);

            return back()->with('erreur', 'La mise à jour de la base a échoué : '.mb_substr($e->getMessage(), 0, 500));
        }

        activity('Serveur')
            ->causedBy($request->user())
            ->withProperties(['migrations' => $attente])
            ->log('Mise à jour de la base');

        return back()->with('succes', count($attente).' mise(s) à jour de la base appliquée(s).');
    }

    /** @return list<string> */
    private function migrationsEnAttente(PreflightService $preflight): array
    {
        try {
            return $preflight->migrationsEnAttente();
        } catch (Throwable) {
            return []; // Base injoignable : signalé par les contrôles.
        }
    }

    /** @return list<array{fichier: string, le: Carbon, taille: int}> les 5 archives les plus récentes du serveur */
    private function sauvegardes(): array
    {
        try {
            $disque = Storage::disk('backups');

            return collect($disque->files(config('backup.backup.name')))
                ->filter(fn ($f) => str_ends_with($f, '.zip'))
                ->map(fn ($f) => ['fichier' => basename($f), 'le' => Carbon::createFromTimestamp($disque->lastModified($f), config('app.timezone')), 'taille' => $disque->size($f)])
                ->sortByDesc('le')->take(5)->values()->all();
        } catch (Throwable) {
            return [];
        }
    }
}
