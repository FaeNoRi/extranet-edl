<?php

namespace App\Http\Requests\Admin;

use App\Models\Referentiel;
use App\Support\CodeStage;
use App\Support\NiveauxReferentiel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferentielRequest extends FormRequest
{
    public const MODULES = [
        'Bases', 'Conjugaison', 'Grammaire', 'Prononciation',
        'Methodologie', 'Vocabulaire', 'Au Quotidien',
    ];

    public function rules(): array
    {
        $referentiel = $this->route('referentiel');
        $langue = $this->input('langue') ?: null;

        return [
            'module' => ['required', Rule::in(self::MODULES)],
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
            'badge' => ['nullable', 'string', 'max:255'],
            'niveaux' => ['array'],
            'niveaux.*' => [Rule::in(NiveauxReferentiel::cles())],
        ];
    }

    /** @return array<string, mixed> */
    public function donnees(): array
    {
        return array_merge(
            ['niveaux' => [], 'badge' => null],
            $this->validated(),
            ['langue' => $this->input('langue') ?: null],
        );
    }

    public function messages(): array
    {
        return ['code.unique' => 'Ce code existe déjà pour cette langue.'];
    }
}
