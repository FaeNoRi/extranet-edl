<?php

namespace App\Services\Gescof;

use App\Enums\CodeProduit;
use App\Enums\Role;
use App\Models\Client;
use App\Models\GescofImport;
use App\Models\PasswordResetToken;
use App\Models\SessionFormation;
use App\Models\User;
use App\Notifications\PasswordSetupLink;
use App\Services\LoginGenerator;
use App\Support\CodeStage;
use App\Support\Nom;
use App\Support\SpreadsheetReader;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Import de l'export GESCOF (inscriptions stagiaires).
 *
 * Colonnes attendues (en-têtes insensibles à la casse/accents) :
 *   Nom, Prenom, NomClient, CodeProduit, LibelleStage, NumSession, Email,
 *   ListeItv, AccesPlateforme
 *
 * Ne gère que les inscriptions : les séances, le planning et les détails de
 * session sont saisis via les formulaires admin/formateur.
 *
 * Règles (cahier des charges §1.2) :
 *  - ignorer les lignes « AccesPlateforme » ≠ Oui ;
 *  - ignorer les stages ponctuels (code « -ST », dont OP-ST) ;
 *  - ignorer ce qui ne relève ni de FPC ni de OP (CLSH, immersion scolaire…) ;
 *  - un login par (stagiaire, session) ; une adresse e-mail peut servir à
 *    plusieurs comptes ;
 *  - aucune suppression : les inscriptions absentes de l'import sont marquées
 *    « disparu_import_at », pas supprimées.
 */
class GescofImporter
{
    /** @var Collection<string, User> formateurs indexés par clé de nom */
    private Collection $formateursParCle;

    /** @var array<int, list<int>> identifiants stagiaires vus par session */
    private array $vus = [];

    /** @var list<int> identifiants des sessions créées pendant l'exécution */
    private array $sessionsCreeesIds = [];

    public function __construct(
        private readonly LoginGenerator $logins = new LoginGenerator,
    ) {}

    public function simuler(string $chemin, ?User $auteur = null): GescofImportReport
    {
        return $this->executer($chemin, false, $auteur, false);
    }

    public function appliquer(string $chemin, ?User $auteur = null, bool $envoyerAcces = false): GescofImportReport
    {
        return $this->executer($chemin, true, $auteur, $envoyerAcces);
    }

    private function executer(string $chemin, bool $appliquer, ?User $auteur, bool $envoyerAcces): GescofImportReport
    {
        $rapport = new GescofImportReport(basename($chemin), $appliquer);
        $this->vus = [];
        $this->sessionsCreeesIds = [];
        $this->formateursParCle = User::query()->where('role', Role::Formateur->value)->get()
            ->keyBy(fn (User $f) => Nom::cle($f->prenom.' '.$f->nom));

        $lignes = SpreadsheetReader::read($chemin);

        // Le détail des créations/modifications n'inonde pas le journal :
        // l'import produit un unique enregistrement récapitulatif (GescofImport).
        activity()->withoutLogging(function () use ($lignes, $rapport, $appliquer) {
            DB::beginTransaction();
            try {
                foreach ($lignes as $i => $ligne) {
                    $this->traiterLigne($i + 2, $ligne, $rapport); // +2 : en-tête + base 1
                }

                $this->marquerDisparus($rapport);

                $rapport->sessionsCreees = count(array_unique($this->sessionsCreeesIds));
                $rapport->sessionsMaj = count($this->vus) - $rapport->sessionsCreees;

                $appliquer ? DB::commit() : DB::rollBack();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        });

        // Le rapport (simulation comprise) est toujours journalisé.
        $this->enregistrerRapport($rapport, $auteur);

        if ($appliquer && $envoyerAcces) {
            $this->envoyerLiensAcces($rapport);
        }

        return $rapport;
    }

    /**
     * @param  array<string, string>  $ligne
     */
    private function traiterLigne(int $numLigne, array $ligne, GescofImportReport $rapport): void
    {
        $rapport->lignesLues++;

        $nom = trim($ligne['nom'] ?? '');
        $prenom = trim($ligne['prenom'] ?? '');

        // Rétro-compatibilité : ancien gabarit avec une seule colonne
        // « NomParticipant » (format « NOM Prénom »).
        if ($nom === '' && $prenom === '' && ! empty($ligne['nomparticipant'])) {
            [$prenom, $nom] = Nom::separerPrenomNom($ligne['nomparticipant']);
        }

        $participant = trim("$prenom $nom");

        if ($participant === '' || Str::contains(Nom::cle($participant), 'definir')) {
            $rapport->ignorer($numLigne, 'participant_absent', 'Participant non renseigné (« À définir »).');

            return;
        }

        if (! $this->estOui($ligne['accesplateforme'] ?? $ligne['accesplatforme'] ?? '')) {
            $rapport->ignorer($numLigne, 'acces_refuse', "Accès plateforme ≠ Oui pour $participant.");

            return;
        }

        $code = CodeStage::analyser($ligne['codeproduit'] ?? '');
        if (! $code->eligiblePlateforme()) {
            $rapport->ignorer($numLigne, 'hors_perimetre', "$participant : {$code->raisonExclusion()} ({$code->brut}).");

            return;
        }

        $numSession = trim($ligne['numsession'] ?? '');
        if ($numSession === '') {
            $rapport->ignorer($numLigne, 'session_absente', "Numéro de session manquant pour $participant.");

            return;
        }

        $email = trim($ligne['email'] ?? '');
        $emailValide = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

        $client = $this->resoudreClient($ligne['nomclient'] ?? '', $email, $emailValide);
        $session = $this->resoudreSession($numSession, $ligne, $code, $client, $rapport);
        $this->affecterFormateurs($session, $ligne['listeitv'] ?? '', $numLigne, $rapport);

        $this->resoudreStagiaire($numLigne, $nom, $prenom, $email, $emailValide, $code->produit, $session, $rapport);
    }

    private function resoudreClient(string $nomClient, string $email, bool $emailValide): ?Client
    {
        $nomClient = trim($nomClient);
        if ($nomClient === '' || Str::lower($nomClient) === 'a definir') {
            return null;
        }

        $client = Client::firstOrNew(['nom' => $nomClient]);
        if ($emailValide && ! $client->email) {
            $client->email = $email;
        }
        $client->save();

        return $client;
    }

    /**
     * @param  array<string, string>  $ligne
     */
    private function resoudreSession(string $numSession, array $ligne, CodeStage $code, ?Client $client, GescofImportReport $rapport): SessionFormation
    {
        $session = SessionFormation::firstOrNew(['num_GESCOF' => $numSession]);
        $nouvelle = ! $session->exists;

        $session->fill([
            'nom' => trim($ligne['libellestage'] ?? '') ?: ($session->nom ?? $numSession),
            'code_stage' => $code->brut,
            'code_produit' => $code->produit ?? CodeProduit::Op,
            'langue' => $code->langue ?? $session->langue ?? 'Anglais',
            'client_id' => $client?->id ?? $session->client_id,
            'intervenants_import' => trim($ligne['listeitv'] ?? '') ?: $session->intervenants_import,
            'gescof_importe_at' => Carbon::now(),
        ]);
        $session->save();

        if ($nouvelle) {
            $this->sessionsCreeesIds[] = $session->id;
        }
        $this->vus[$session->id] ??= [];

        return $session;
    }

    private function affecterFormateurs(SessionFormation $session, string $listeItv, int $numLigne, GescofImportReport $rapport): void
    {
        $reconnus = [];

        foreach (preg_split('/\s+-\s+/', trim($listeItv)) ?: [] as $jeton) {
            $jeton = trim($jeton);
            $cle = Nom::cle($jeton);

            if ($jeton === '' || Str::contains($cle, ['theme', 'seance'])) {
                continue;
            }

            $formateur = $this->formateursParCle->get($cle);
            if ($formateur) {
                $reconnus[$formateur->id] = $formateur;
            } else {
                $rapport->anomalie($numLigne, 'formateur_non_reconnu', "Formateur non reconnu : « $jeton » (session {$session->num_GESCOF}).");
            }
        }

        if ($reconnus === []) {
            return;
        }

        foreach ($reconnus as $id => $_) {
            $session->formateurs()->syncWithoutDetaching([$id => ['principal' => false]]);
        }

        if (! $session->formateur_id) {
            $premier = array_key_first($reconnus);
            $session->formateur_id = $premier;
            $session->save();
            $session->formateurs()->updateExistingPivot($premier, ['principal' => true]);
        }
    }

    private function resoudreStagiaire(
        int $numLigne,
        string $nom,
        string $prenom,
        string $email,
        bool $emailValide,
        ?CodeProduit $produit,
        SessionFormation $session,
        GescofImportReport $rapport,
    ): void {
        $nom = mb_strtoupper($nom);
        $prenom = Str::title($prenom);
        $role = $produit === CodeProduit::Fpc ? Role::StagiaireFpc : Role::StagiaireOp;

        $existant = User::withTrashed()
            ->whereIn('role', [Role::StagiaireOp->value, Role::StagiaireFpc->value])
            ->whereHas('sessionFormations', fn ($q) => $q->where('session_formations.id', $session->id))
            ->get()
            ->first(fn (User $u) => Nom::cle($u->nom) === Nom::cle($nom) && Nom::cle($u->prenom) === Nom::cle($prenom));

        if ($existant) {
            $reactive = false;

            if ($existant->trashed()) {
                $existant->restore();
                $reactive = true;
            }

            $pivot = $existant->sessionFormations()->where('session_formations.id', $session->id)->first()?->pivot;
            if ($pivot?->disparu_import_at) {
                $existant->sessionFormations()->updateExistingPivot($session->id, ['disparu_import_at' => null]);
                $reactive = true;
            }

            if ($existant->role !== $role) {
                $existant->role = $role;
                $existant->save();
            }

            if ($reactive) {
                $rapport->comptesReactives++;
            }

            $this->vus[$session->id][] = $existant->id;

            return;
        }

        $user = new User([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'login' => $this->logins->generer($prenom, $nom),
            'role' => $role->value,
        ]);
        $user->password = Str::random(40);
        $user->save();

        $session->stagiaires()->syncWithoutDetaching([$user->id]);
        $this->vus[$session->id][] = $user->id;
        $rapport->comptesCrees++;

        if ($emailValide) {
            $rapport->comptesANotifier[] = $user->id;
        } else {
            $rapport->anomalie($numLigne, 'email_invalide', "Compte créé pour $prenom $nom sans e-mail exploitable (« $email ») : lien d'accès non envoyable.");
        }
    }

    private function marquerDisparus(GescofImportReport $rapport): void
    {
        foreach ($this->vus as $sessionId => $vusIds) {
            $session = SessionFormation::find($sessionId);
            if (! $session) {
                continue;
            }

            $disparus = $session->stagiaires()
                ->wherePivotNull('disparu_import_at')
                ->whereNotIn('users.id', $vusIds)
                ->pluck('users.id');

            foreach ($disparus as $userId) {
                $session->stagiaires()->updateExistingPivot($userId, ['disparu_import_at' => Carbon::now()]);
                $rapport->comptesDisparus++;
            }
        }
    }

    private function enregistrerRapport(GescofImportReport $rapport, ?User $auteur): GescofImport
    {
        return GescofImport::create([
            'user_id' => $auteur?->id,
            'fichier_nom' => $rapport->fichierNom,
            'applique' => $rapport->applique,
            'lignes_lues' => $rapport->lignesLues,
            'lignes_ignorees' => $rapport->lignesIgnorees,
            'comptes_crees' => $rapport->comptesCrees,
            'comptes_reactives' => $rapport->comptesReactives,
            'comptes_disparus' => $rapport->comptesDisparus,
            'sessions_creees' => $rapport->sessionsCreees,
            'sessions_maj' => $rapport->sessionsMaj,
            'anomalies' => $rapport->anomalies,
        ]);
    }

    private function envoyerLiensAcces(GescofImportReport $rapport): void
    {
        User::whereIn('id', $rapport->comptesANotifier)->each(function (User $user) {
            $token = PasswordResetToken::issueFor($user);
            $user->notify(new PasswordSetupLink($token, nouveauCompte: true));
        });
    }

    private function estOui(string $valeur): bool
    {
        return in_array(
            SpreadsheetReader::sansAccents(Str::lower(trim($valeur))),
            ['oui', 'o', 'yes', 'y', '1', 'true', 'vrai'],
            true,
        );
    }
}
