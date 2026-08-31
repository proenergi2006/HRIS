<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOnboardingTask extends Model
{
    protected $fillable = [
        'employee_id', 'onboarding_checklist_item_id', 'is_done', 'done_at', 'done_by_user_id', 'notes',
    ];

    protected $casts = ['is_done' => 'boolean', 'done_at' => 'datetime'];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function item(): BelongsTo     { return $this->belongsTo(OnboardingChecklistItem::class, 'onboarding_checklist_item_id'); }
    public function doneBy(): BelongsTo   { return $this->belongsTo(User::class, 'done_by_user_id'); }
}
