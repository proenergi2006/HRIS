<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProbationReview extends Model
{
    protected $fillable = ['employee_id', 'review_date', 'decision', 'performance_notes', 'extended_until', 'reviewed_by_user_id'];

    protected $casts = [
        'review_date'    => 'date',
        'extended_until' => 'date',
    ];

    public static array $decisionLabels = [
        'passed'   => 'Lulus (Permanen)',
        'extended' => 'Diperpanjang',
        'failed'   => 'Tidak Lulus',
    ];

    public static array $decisionBadges = [
        'passed'   => 'success',
        'extended' => 'warning',
        'failed'   => 'danger',
    ];

    public function employee(): BelongsTo   { return $this->belongsTo(Employee::class); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
}
