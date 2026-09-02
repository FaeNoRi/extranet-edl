<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id', 'libelle', 'type', 'options', 'obligatoire', 'ordre',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'obligatoire' => 'boolean',
            'ordre' => 'integer',
        ];
    }

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(QuestionnaireReponse::class);
    }
}
