<?php

namespace App\Models\Appraisal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback360Answer extends Model
{
    protected $table = 'feedback_360_answers';

    protected $fillable = ['review_id', 'question_key', 'rating', 'comment'];

    public function review(): BelongsTo { return $this->belongsTo(Feedback360Review::class, 'review_id'); }
}
