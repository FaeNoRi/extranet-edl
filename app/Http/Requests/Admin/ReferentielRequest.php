<?php

namespace App\Http\Requests\Admin;

use App\Models\Referentiel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferentielRequest extends FormRequest
{
    public const MODULES = [
        'Bases', 'Conjugaison', 'Grammaire', 'Prononciation',
        'Methodologie', 'Vocabulaire', 'Au Quotidien',
    ];

    public const NIVEAUX = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function rules(): array
    {
        $referentiel = $this->route('referentiel');

        return [
            'module' => ['required', Rule::in(self::MODULES)],
            'code' => [
                'required', 'string', 'max:255',
                Rule::unique(Referentiel::class, 'code')->ignore($referentiel),
            ],
            'contenu' => ['required', 'string', 'max:5000'],
            'niveaux' => ['array'],
            'niveaux.*' => [Rule::in(self::NIVEAUX)],
        ];
    }
}
