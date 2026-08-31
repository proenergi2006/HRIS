<?php

namespace App\Models\Appraisal;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Continuous Feedback / 1-on-1 — catatan ngobrol rutin manager-karyawan, di luar siklus appraisal formal. */
class PerformanceCheckin extends Model
{
    protected $table = 'performance_checkins';

    protected $fillable = [
        'employee_id', 'created_by_user_id', 'checkin_date', 'notes',
        'action_items', 'employee_comment', 'next_checkin_date',
    ];

    protected $casts = [
        'checkin_date'      => 'date',
        'next_checkin_date' => 'date',
    ];

    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
