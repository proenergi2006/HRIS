<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\AsApprovableRequest;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PromotionRotationRequest extends Model implements Approvable
{
    use AsApprovableRequest, LogsActivity, HasApprovalWorkflow, HasHashid;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'request_type', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('promotion_rotation');
    }

    protected $fillable = [
        'employee_id', 'company_id', 'requested_by_user_id', 'request_type',
        'from_position_id', 'to_position_id', 'from_company_id', 'to_company_id',
        'effective_date', 'reason', 'status', 'notes',
    ];

    protected $casts = ['effective_date' => 'date'];

    public static array $typeLabels = [
        'promotion' => 'Promosi',
        'rotation'  => 'Rotasi',
        'mutation'  => 'Mutasi',
        'demotion'  => 'Demosi',
    ];

    public function fromPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'from_position_id');
    }

    public function toPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'to_position_id');
    }

    public function approvalTransactionType(): string
    {
        return 'promotion_rotation_request';
    }

    public function approvalSummary(): string
    {
        return (self::$typeLabels[$this->request_type] ?? $this->request_type)
            . ' — ' . ($this->employee?->name ?? '?');
    }

    /** Terapkan perubahan jabatan/perusahaan saat disetujui. */
    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();

        if ($employee = $this->employee) {
            $employee->forceFill(array_filter([
                'position_id' => $this->to_position_id,
                'company_id'  => $this->to_company_id,
            ]))->save();

            $employee->orgExperiences()->create([
                'company_id'    => $this->to_company_id ?? $employee->company_id,
                'position_id'   => $this->to_position_id,
                'position_name' => $this->toPosition?->name,
                'change_type'   => $this->request_type === 'demotion' ? 'demotion' : $this->request_type,
                'start_date'    => $this->effective_date ?? now()->toDateString(),
                'remarks'       => 'Dari Promosi & Rotasi #' . $this->id,
            ]);
        }
    }
}
