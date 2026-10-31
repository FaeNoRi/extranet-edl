<?php

namespace App\Http\Requests\Admin;

use App\Models\Referentiel;
use App\Support\CodeStage;
use App\Support\NiveauxReferentiel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReferentielRequest extends FormRequest
{
    /** @deprecated Utiliser Referentiel::MODULES. */
    public const MODULES = Referentiel::MODULES;

    public function rules(): array
    {
        $referentiel = $this->route('referentiel');
        $langue = $this->input('langue') ?: null;

        // Une entrée archivée peut porter un module de l'ancienne trame : on le laisse modifiable.
        $modules = array_filter([...Referentiel::MODULES, $referentiel?->module]);

        return [
            'module' => ['required', Rule::in($modules)],
            // Vide = toutes langues.
            'langue' => ['nullable', Rule::in(CodeStage::langues())],
            // Un code est unique dans une langue (le même code peut exister en anglais et en espagnol).
            'code' => [
                'required', 'string', 'max:255',
                Rule::unique(Referentiel::class, 'code')
                    ->ignore($referentiel)
                    ->where(fn ($q) => $langue === null ? $q->whereNull('langue') : $q->where('langue', $langue)),
            ],
            'contenu' => ['required', 'string', 'max:5000'],
            // Saisie « COM, RH, ORAL » : chaque badge est contrôlé après découpage.
            'badges' => ['nullable', 'string', 'max:500'],
            'niveaux' => ['array'],
            'niveaux.*' => [Rule::in(NiveauxReferentiel::cles())],
            'archivee' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            foreach (Referentiel::normaliserBadges($this->input('badges')) as $badge) {
                if (mb_strlen($badge) > 30) {
                    $validator->errors()->add('badges', "Le badge « {$badge} » dépasse 30 caractères.");
                }
            }
        }];
    }

    /** @return array<string, mixed> */
    public function donnees(): array
    {
        $referentiel = $this->route('referentiel');
        $archivee = $this->boolean('archivee');

        return [
            'module' => $this->validated('module'),
            'langue' => $this->input('langue') ?: null,
            'code' => $this->validated('code'),
            'contenu' => $this->validated('contenu'),
            'badges' => Referentiel::normaliserBadges($this->input('badges')),
            'niveaux' => $this->validated('niveaux', []),
            // Garde la date d'archivage d'origine si l'entrée l'était déjà.
            'archive_le' => $archivee ? ($referentiel?->archive_le ?? now()) : null,
        ];
    }

    public function messages(): array
    {
        return ['code.unique' => 'Ce code existe déjà pour cette langue.'];
    }
}
