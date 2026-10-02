<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Export des données personnelles d'un utilisateur (droit d'accès et de
 * portabilité, art. 15 et 20 du RGPD). Aucune donnée d'un tiers n'y figure
 * (noms des autres stagiaires, par exemple), ni les secrets techniques
 * (empreinte du mot de passe, jetons).
 */
class ExportDonneesUtilisateurService
{
    /** @return array<string, mixed> */
    public function exporter(User $user, User $demandeur): array
    {
        return [
            'export' => [
                'genere_le' => now()->toIso8601String(),
                'genere_par' => $demandeur->login,
                'responsable_de_traitement' => config('edl.structure.nom'),
                'fondement' => 'Droit d\'accès (art. 15) et droit à la portabilité (art. 20) du RGPD',
                'version_du_format' => 1,
            ],
            'compte' => $this->compte($user),
            'sessions_de_formation' => $this->sessionsDeFormation($user),
            'formations_animees' => $this->formationsAnimees($user),
            'fiches_pedagogiques_individuelles' => $this->fichesIndividuelles($user),
            'emargements' => $this->emargements($user),
            'questionnaires' => $this->questionnaires($user),
            'referentiel_consulte' => $this->referentielConsulte($user),
            'documents_attribues' => $user->documents()->orderBy('nom')->get()->map(fn ($d) => [
                'nom' => $d->nom,
                'categorie' => $d->categorie,
            ])->all(),
            'journal' => $this->journal($user),
            'connexions_techniques' => $this->connexions($user),
            'non_inclus' => [
                'Empreinte du mot de passe et jetons de connexion ou de réinitialisation (secrets techniques).',
                'Données concernant d\'autres personnes (noms des autres stagiaires ou formateurs dans les séances, par exemple).',
            ],
        ];
    }

    private function iso(mixed $date): ?string
    {
        return $date ? Carbon::parse($date)->toIso8601String() : null;
    }

    /** @return array<string, mixed> */
    private function compte(User $user): array
    {
        $compte = [
            'identifiant' => $user->login,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'email' => $user->email,
            'role' => $user->role?->value,
            'cree_le' => $this->iso($user->created_at),
            'modifie_le' => $this->iso($user->updated_at),
            'supprime_le' => $this->iso($user->deleted_at),
        ];

        if ($user->isFormateur()) {
            $compte += [
                'intervient_en_fpc' => $user->formateur_fpc,
                'intervient_en_op' => $user->formateur_op,
                'presentation' => $user->presentation,
                'photo_enregistree' => (bool) $user->photo_path,
            ];
        }

        return $compte;
    }

    /** @return list<array<string, mixed>> */
    private function sessionsDeFormation(User $user): array
    {
        return $user->sessionFormations()->with('formateur')->orderBy('num_GESCOF')->get()->map(fn ($s) => [
            'numero_gescof' => $s->num_GESCOF,
            'intitule' => $s->nom,
            'produit' => $s->code_produit?->value,
            'langue' => $s->langue,
            'formateur_referent' => $s->formateur?->nom_complet,
            'rattache_le' => $this->iso($s->pivot->created_at),
            'absent_du_dernier_import_le' => $this->iso($s->pivot->disparu_import_at),
        ])->all();
    }

    /** @return list<array<string, mixed>> */
    private function formationsAnimees(User $user): array
    {
        if (! $user->isFormateur()) {
            return [];
        }

        $seances = $user->seancesAnimees()->with('sessionFormation')->orderBy('date')->get()->groupBy('session_formation_id');

        return $user->sessionsPourFormateur()->map(fn ($s) => [
            'numero_gescof' => $s->num_GESCOF,
            'intitule' => $s->nom,
            'referent' => $s->formateur_id === $user->id,
            'seances_animees' => ($seances->get($s->id) ?? collect())
                ->map(fn ($seance) => $seance->date->toDateString())->values()->all(),
        ])->values()->all();
    }

    /** @return list<array<string, mixed>> */
    private function fichesIndividuelles(User $user): array
    {
        return $user->fichesPedagogiques()->with('sessionFormation', 'formateur')->orderBy('date')->get()->map(fn ($f) => [
            'date' => $f->date->toDateString(),
            'session' => $f->sessionFormation?->num_GESCOF,
            'formateur' => $f->formateur?->nom_complet,
            'langue' => $f->langue,
            'objectifs' => $f->objectifs,
            'contenu' => $f->contenu,
            'outils' => $f->outils,
            'sources' => $f->sources,
            'analyse_du_formateur' => $f->analyse_seance,
        ])->all();
    }

    /** @return list<array<string, mixed>> */
    private function emargements(User $user): array
    {
        return $user->emargements()->with('seance.sessionFormation')->get()
            ->sortBy(fn ($e) => $e->seance?->date)
            ->map(fn ($e) => [
                'date_seance' => $e->seance?->date->toDateString(),
                'session' => $e->seance?->sessionFormation?->num_GESCOF,
                'present' => $e->present,
                'signe_le' => $this->iso($e->signe_at),
                'commentaire' => $e->commentaire,
                'signature_enregistree' => (bool) $e->signature_path,
            ])->values()->all();
    }

    /** @return list<array<string, mixed>> */
    private function questionnaires(User $user): array
    {
        $soumissions = DB::table('questionnaire_soumissions')
            ->where('user_id', $user->id)
            ->pluck('soumis_at', 'questionnaire_id');

        return $user->questionnaireReponses()->with('question', 'questionnaire')->get()
            ->groupBy('questionnaire_id')
            ->map(function ($reponses, $questionnaireId) use ($soumissions) {
                $questionnaire = $reponses->first()->questionnaire;

                return [
                    'questionnaire' => $questionnaire?->titre,
                    'type' => $questionnaire?->type?->value,
                    'soumis_le' => $this->iso($soumissions[$questionnaireId] ?? null),
                    'reponses' => $reponses->sortBy(fn ($r) => $r->question?->ordre)->map(fn ($r) => [
                        'question' => $r->question?->libelle,
                        'reponse' => $r->valeur,
                    ])->values()->all(),
                ];
            })->values()->all();
    }

    /** @return list<array<string, mixed>> */
    private function referentielConsulte(User $user): array
    {
        return $user->referentiels()->orderBy('module')->get()->map(fn ($r) => [
            'module' => $r->module,
            'contenu' => $r->contenu,
            'consulte_le' => $this->iso($r->pivot->consulte_at),
        ])->all();
    }

    /**
     * Les propriétés d'une entrée ne sont reprises que si l'utilisateur en est
     * l'objet : quand il en est l'auteur, elles décrivent des données d'autrui.
     *
     * @return list<array<string, mixed>>
     */
    private function journal(User $user): array
    {
        return Activity::query()
            ->where(fn ($q) => $q
                ->where(fn ($c) => $c->where('causer_type', $user->getMorphClass())->where('causer_id', $user->id))
                ->orWhere(fn ($s) => $s->where('subject_type', $user->getMorphClass())->where('subject_id', $user->id)))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->map(function (Activity $a) use ($user) {
                $entree = [
                    'date' => $this->iso($a->created_at),
                    'journal' => $a->log_name,
                    'evenement' => $a->event,
                    'description' => $a->description,
                    'objet' => $a->subject_type ? class_basename($a->subject_type).' #'.$a->subject_id : null,
                    'role_dans_l_action' => $a->causer_id === $user->id && $a->causer_type === $user->getMorphClass() ? 'auteur' : 'concerne',
                ];

                if ($a->subject_id === $user->id && $a->subject_type === $user->getMorphClass()) {
                    $entree['modifications'] = $a->properties->toArray();
                }

                return $entree;
            })->all();
    }

    /** @return list<array<string, mixed>> */
    private function connexions(User $user): array
    {
        return DB::table('sessions')->where('user_id', $user->id)->get()->map(fn ($s) => [
            'adresse_ip' => $s->ip_address,
            'navigateur' => $s->user_agent,
            'derniere_activite' => Carbon::createFromTimestamp($s->last_activity)->toIso8601String(),
        ])->all();
    }
}
