<?php

namespace App\Http\Requests\Formateur;

use App\Models\SessionFormation;
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

        if (! $session || ! $user?->isFormateur()) {
            return false;
        }

        return $session->formateur_id === $user->id
            || $session->formateurs()->whereKey($user->id)->exists();
    }

    public function rules(): array
    {
        $session = $this->sessionFormation();
        $objectifsAutorises = $session ? OptionsSeance::objectifsPour($session) : OptionsSeance::OBJECTIFS;

        return [
            'session_formation_id' => ['required', 'exists:session_formations,id'],
            'user_id' => [
                Rule::requiredIf(fn () => $session?->isFpc()),
                'nullable',
                Rule::exists('users', 'id')->where(
                    fn ($q) => $q->whereIn('role', ['stagiaire_op', 'stagiaire_fpc'])
                ),
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
            'ressources.*' => ['exists:ressources,id'],
            'fichiers_transmis' => ['array'],
            'fichiers_transmis.*' => ['file', 'max:51200'],
            'fichiers_internes' => ['array'],
            'fichiers_internes.*' => ['file', 'max:51200'],
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
