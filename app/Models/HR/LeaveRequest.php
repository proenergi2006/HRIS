<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days',
        'reason', 'attachment_path', 'status',
        'manager_approved_by', 'manager_approved_at', 'manager_notes',
        'hr_approved_by', 'hr_approved_at', 'hr_notes',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'manager_approved_at' => 'datetime',
        'hr_approved_at'      => 'datetime',
        'total_days'          => 'decimal:1',
    ];

    public static array $statusLabels = [
        'draft'            => 'Draft',
        'submitted'        => 'Menunggu Atasan',
        'approved_manager' => 'Menunggu HR',
        'approved_hr'      => 'Disetujui',
        'rejected'         => 'Ditolak',
    ];

    public static array $statusBadges = [
        'draft'            => 'secondary',
        'submitted'        => 'warning',
        'approved_manager' => 'info',
        'approved_hr'      => 'success',
        'rejected'         => 'danger',
    ];

    public function isDraft(): bool           { return $this->status === 'draft'; }
    public function isSubmitted(): bool       { return $this->status === 'submitted'; }
    public function isApprovedManager(): bool { return $this->status === 'approved_manager'; }
    public function isApproved(): bool        { return $this->status === 'approved_hr'; }
    public function isRejected(): bool        { return $this->status === 'rejected'; }

    public function employee(): BelongsTo    { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo   { return $this->belongsTo(LeaveType::class); }
    public function managerApprover(): BelongsTo { return $this->belongsTo(User::class, 'manager_approved_by'); }
    public function hrApprover(): BelongsTo      { return $this->belongsTo(User::class, 'hr_approved_by'); }
}
