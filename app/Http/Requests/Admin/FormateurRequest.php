<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormateurRequest extends FormRequest
{
    public function rules(): array
    {
        $formateur = $this->route('formateur');

        return [
            'prenom' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'login' => [
                'required', 'string', 'max:255', 'regex:/^[\pL\pN._-]+$/u',
                Rule::unique(User::class, 'login')->ignore($formateur),
            ],
            'email' => ['required', 'email', 'max:255'],
            'formateur_fpc' => ['boolean'],
            'formateur_op' => ['boolean'],
            'presentation' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return ['login' => 'identifiant'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'formateur_fpc' => $this->boolean('formateur_fpc'),
            'formateur_op' => $this->boolean('formateur_op'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->boolean('formateur_fpc') && ! $this->boolean('formateur_op')) {
                $validator->errors()->add('formateur_op', 'Le formateur doit intervenir en FPC et/ou en OP.');
            }
        });
    }
}
