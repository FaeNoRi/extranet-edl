<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdministrateurRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'prenom' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'login' => [
                'required', 'string', 'max:255', 'regex:/^[\pL\pN._-]+$/u',
                Rule::unique(User::class, 'login')->ignore($this->route('administrateur')),
            ],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['login' => 'identifiant'];
    }
}
