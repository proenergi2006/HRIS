<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePreEmploymentTask extends Model
{
    protected $table = 'candidate_preemployment_tasks';

    protected $fillable = [
        'candidate_id', 'preemployment_checklist_item_id',
        'is_done', 'done_at', 'done_by_user_id', 'notes',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'done_at' => 'datetime',
    ];

    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function item(): BelongsTo { return $this->belongsTo(PreEmploymentChecklistItem::class, 'preemployment_checklist_item_id'); }
    public function doneBy(): BelongsTo { return $this->belongsTo(User::class, 'done_by_user_id'); }
}
