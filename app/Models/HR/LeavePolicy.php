<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Level;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeavePolicy extends Model
{
    protected $fillable = [
        'company_id', 'leave_type_id', 'level_id', 'min_years_service',
        'quota_days', 'carry_forward_max_days', 'carry_forward_expire_month', 'is_active',
    ];

    protected $casts = [
        'quota_days'              => 'decimal:1',
        'carry_forward_max_days'  => 'decimal:1',
        'is_active'                => 'boolean',
    ];

    public function company(): BelongsTo   { return $this->belongsTo(Company::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function level(): BelongsTo     { return $this->belongsTo(Level::class); }

    /**
     * Cari policy paling spesifik untuk karyawan: company match > null,
     * level match > null, min_years_service tertinggi yang masih <= masa kerja karyawan.
     */
    public static function resolveFor(Employee $employee, LeaveType $leaveType, int $year): ?self
    {
        if (! $employee->start_date) {
            return null;
        }

        $tenureYears = (int) $employee->start_date->diffInYears(Carbon::create($year, 1, 1));

        return static::where('leave_type_id', $leaveType->id)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $employee->company_id))
            ->where(fn ($q) => $q->whereNull('level_id')->orWhere('level_id', $employee->level_id))
            ->where('min_years_service', '<=', $tenureYears)
            ->orderByRaw('company_id IS NULL') // company spesifik dulu
            ->orderByRaw('level_id IS NULL')   // level spesifik dulu
            ->orderByDesc('min_years_service') // tenure tertinggi yang masih terpenuhi
            ->first();
    }
}
