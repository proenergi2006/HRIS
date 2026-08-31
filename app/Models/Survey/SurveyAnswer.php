<?php

namespace App\Models\Survey;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAnswer extends Model
{
    protected $fillable = ['survey_response_id', 'survey_question_id', 'value', 'value_json'];

    protected $casts = ['value_json' => 'array'];

    public function response(): BelongsTo { return $this->belongsTo(SurveyResponse::class, 'survey_response_id'); }
    public function question(): BelongsTo { return $this->belongsTo(SurveyQuestion::class, 'survey_question_id'); }
}
