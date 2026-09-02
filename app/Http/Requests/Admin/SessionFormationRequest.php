<?php

namespace App\Http\Requests\Admin;

use App\Enums\CodeProduit;
use App\Models\SessionFormation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class SessionFormationRequest extends FormRequest
{
    public function rules(): array
    {
        $session = $this->route('session');

        return [
            'num_GESCOF' => [
                'required', 'string', 'max:255',
                Rule::unique(SessionFormation::class, 'num_GESCOF')->ignore($session),
            ],
            'nom' => ['required', 'string', 'max:255'],
            'code_stage' => ['nullable', 'string', 'max:255'],
            'code_produit' => ['required', new Enum(CodeProduit::class)],
            'langue' => ['required', 'string', 'max:255'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'nouveau_client' => ['nullable', 'string', 'max:255'],
            'formateur_id' => ['nullable', 'exists:users,id'],
            'formateurs' => ['array'],
            'formateurs.*' => ['exists:users,id'],
            'objectifs' => ['nullable', 'string', 'max:5000'],
            'distanciel' => ['boolean'],
            'lien_teams' => ['nullable', 'url', 'max:255'],
            'rythme_op' => ['nullable', Rule::in(['trimestre', 'annee'])],
            'dates_planning' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'num_GESCOF' => 'numéro GESCOF',
            'code_produit' => 'code produit',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['distanciel' => $this->boolean('distanciel')]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('code_produit') === CodeProduit::Op->value && ! $this->input('rythme_op')) {
                $validator->errors()->add('rythme_op', 'Le rythme (trimestre ou année) est requis pour une session OP.');
            }
        });
    }
}
