<?php

namespace App\Models\Survey;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyQuestion extends Model
{
    protected $fillable = ['survey_id', 'text', 'type', 'options', 'is_required', 'sort_order'];

    protected $casts = ['options' => 'array', 'is_required' => 'boolean'];

    public static array $typeLabels = [
        'scale'  => 'Skala 0-10',
        'text'   => 'Teks Bebas',
        'single' => 'Pilihan Tunggal',
        'multi'  => 'Pilihan Ganda',
    ];

    public function survey(): BelongsTo { return $this->belongsTo(Survey::class); }
}
