<?php

namespace App\Models\Competency;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** Level kompetensi aktual seorang karyawan (current-state, 1 baris per employee+competency). */
class EmployeeCompetency extends Model
{
    use LogsActivity;

    protected $fillable = [
        'employee_id', 'competency_id', 'actual_level', 'assessed_on', 'assessor_user_id', 'notes',
    ];

    protected $casts = [
        'actual_level' => 'integer',
        'assessed_on'  => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['actual_level', 'assessed_on', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('competency');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_user_id');
    }
}
