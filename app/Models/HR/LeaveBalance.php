<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = ['employee_id', 'leave_type_id', 'year', 'allocated', 'used'];

    protected $casts = ['allocated' => 'decimal:1', 'used' => 'decimal:1'];

    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }

    public function getRemaining(): float
    {
        return max(0, (float) $this->allocated - (float) $this->used);
    }

    public static function forEmployee(int $employeeId, int $leaveTypeId, int $year): self
    {
        return static::firstOrCreate(
            ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId, 'year' => $year],
            ['allocated' => LeaveType::find($leaveTypeId)?->days_per_year ?? 0, 'used' => 0]
        );
    }
}
