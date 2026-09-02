<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireReponse extends Model
{
    use HasFactory;

    protected $table = 'questionnaire_reponses';

    protected $fillable = [
        'questionnaire_id', 'questionnaire_question_id', 'user_id', 'valeur',
    ];

    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireQuestion::class, 'questionnaire_question_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
