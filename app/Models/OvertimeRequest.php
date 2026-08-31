<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Models\HR\AttendanceRecord;
use App\Traits\AsApprovableRequest;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OvertimeRequest extends Model implements Approvable
{
    use AsApprovableRequest, LogsActivity, HasApprovalWorkflow, HasHashid;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'planned_hours', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('overtime');
    }

    protected $fillable = [
        'employee_id', 'company_id', 'requested_by_user_id',
        'date', 'planned_hours', 'reason', 'status', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public static array $statusLabels = [
        'draft'     => 'Draft',
        'pending'   => 'Menunggu Persetujuan',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    public static array $statusBadges = [
        'draft'     => 'secondary',
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'cancelled' => 'secondary',
    ];

    public function isDraft(): bool { return $this->status === 'draft'; }

    public function approvalTransactionType(): string
    {
        return 'overtime_request';
    }

    public function approvalSummary(): string
    {
        return 'Lembur — ' . ($this->employee?->name ?? '?') . ' · ' . $this->date?->format('d/m/Y')
            . ' · ' . rtrim(rtrim(number_format((float) $this->planned_hours, 1), '0'), '.') . ' jam';
    }

    /**
     * Terapkan ke AttendanceRecord.overtime_minutes (sumber data yang sama dipakai
     * halaman HR > Lembur & Tunjangan Lembur payroll) saat disetujui.
     */
    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();

        AttendanceRecord::updateOrCreate(
            ['employee_id' => $this->employee_id, 'date' => $this->date->toDateString()],
            [
                'company_id'       => $this->company_id ?? $this->employee?->company_id,
                'overtime_minutes' => (int) round($this->planned_hours * 60),
            ]
        );
    }
}
