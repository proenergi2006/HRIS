<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = ['employee_id', 'leave_type_id', 'year', 'allocated', 'used', 'carried_days', 'carried_expires_on'];

    protected $casts = [
        'allocated'          => 'decimal:1',
        'used'                => 'decimal:1',
        'carried_days'       => 'decimal:1',
        'carried_expires_on' => 'date',
    ];

    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }

    public function getRemaining(): float
    {
        return max(0, (float) $this->allocated - (float) $this->used);
    }

    /**
     * Alokasi default: pakai LeavePolicy (kuota per golongan/masa kerja) bila ada,
     * else fallback ke LeaveType::days_per_year (kompat mundur, tidak ada policy).
     */
    public static function forEmployee(int $employeeId, int $leaveTypeId, int $year): self
    {
        $leaveType = LeaveType::find($leaveTypeId);
        $allocated = $leaveType?->days_per_year ?? 0;

        $employee = Employee::find($employeeId);
        if ($employee && $leaveType) {
            $policy = LeavePolicy::resolveFor($employee, $leaveType, $year);
            if ($policy) {
                $allocated = (float) $policy->quota_days;
            }
        }

        return static::firstOrCreate(
            ['employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId, 'year' => $year],
            ['allocated' => $allocated, 'used' => 0]
        );
    }
}
