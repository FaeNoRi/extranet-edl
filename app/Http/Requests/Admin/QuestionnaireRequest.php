<?php

namespace App\Http\Requests\Admin;

use App\Enums\TypeQuestion;
use App\Enums\TypeQuestionnaire;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class QuestionnaireRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(TypeQuestionnaire::class)],
            'session_formation_id' => ['nullable', 'exists:session_formations,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'actif' => ['boolean'],

            'questions' => ['array'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.libelle' => ['required', 'string', 'max:500'],
            'questions.*.type' => ['required', new Enum(TypeQuestion::class)],
            'questions.*.obligatoire' => ['boolean'],
            'questions.*.options' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'questions.*.libelle' => 'libellé de question',
            'questions.*.type' => 'type de question',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['actif' => $this->boolean('actif')]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('questions', []) as $i => $question) {
                $type = TypeQuestion::tryFrom($question['type'] ?? '');
                if ($type?->attendOptions() && trim($question['options'] ?? '') === '') {
                    $validator->errors()->add("questions.$i.options", 'Indiquez au moins une option (une par ligne).');
                }
            }
        });
    }

    /**
     * @return list<array{id: ?int, libelle: string, type: string, obligatoire: bool, options: ?array}>
     */
    public function questionsNormalisees(): array
    {
        return collect($this->input('questions', []))
            ->values()
            ->map(fn ($q, $i) => [
                'id' => isset($q['id']) ? (int) $q['id'] : null,
                'libelle' => trim($q['libelle']),
                'type' => $q['type'],
                'obligatoire' => filter_var($q['obligatoire'] ?? false, FILTER_VALIDATE_BOOL),
                'ordre' => $i + 1,
                'options' => TypeQuestion::from($q['type'])->attendOptions()
                    ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $q['options'] ?? ''))))
                    : null,
            ])
            ->all();
    }
}
