<?php

namespace App\Http\Requests\Formateur;

use App\Models\SessionFormation;
use App\Rules\FichierAutorise;
use App\Support\OptionsSeance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SeanceRequest extends FormRequest
{
    private ?SessionFormation $sessionCache = null;

    public function authorize(): bool
    {
        $session = $this->sessionFormation();
        $user = $this->user();

        if (! $session || ! $user) {
            return false;
        }

        // L'admin peut modifier la fiche de n'importe quelle séance (aussi
        // exposé via App\Http\Controllers\Admin\SeanceController) ; un
        // formateur reste cantonné aux sessions qu'il encadre.
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isFormateur() && (
            $session->formateur_id === $user->id
            || $session->formateurs()->whereKey($user->id)->exists()
        );
    }

    public function rules(): array
    {
        $session = $this->sessionFormation();
        $objectifsAutorises = $session ? OptionsSeance::objectifsPour($session) : OptionsSeance::OBJECTIFS;

        return [
            'session_formation_id' => ['required', 'exists:session_formations,id'],
            // Fiche FPC individuelle : le stagiaire doit être inscrit à CETTE session.
            'user_id' => [
                Rule::requiredIf(fn () => $session?->isFpc()),
                'nullable',
                Rule::exists('session_formation_user', 'user_id')->where('session_formation_id', $session?->id),
            ],
            'date' => ['required', 'date'],
            'objectifs' => ['array'],
            'objectifs.*' => [Rule::in($objectifsAutorises)],
            'contenu' => ['required', 'string', 'max:10000'],
            'outils' => ['array'],
            'outils.*' => [Rule::in(OptionsSeance::OUTILS)],
            'sources' => ['required', 'string', 'max:5000'],
            'analyse_seance' => ['required', 'string', 'max:10000'],
            'referentiels' => ['array'],
            'referentiels.*' => ['exists:referentiel,id'],
            'ressources' => ['array'],
            // Réutilisation : uniquement des ressources de la même session (sinon on exposerait
            // aux stagiaires les fichiers d'une autre formation).
            'ressources.*' => [Rule::exists('ressources', 'id')->where('session_formation_id', $session?->id)],
            'fichiers_transmis' => ['array'],
            'fichiers_transmis.*' => FichierAutorise::regles(),
            'fichiers_internes' => ['array'],
            'fichiers_internes.*' => FichierAutorise::regles(),
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'stagiaire',
            'analyse_seance' => 'analyse de la séance',
        ];
    }

    public function sessionFormation(): ?SessionFormation
    {
        return $this->sessionCache ??= SessionFormation::find($this->input('session_formation_id'));
    }
}
