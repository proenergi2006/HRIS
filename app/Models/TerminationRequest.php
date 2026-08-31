<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\AsApprovableRequest;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TerminationRequest extends Model implements Approvable
{
    use AsApprovableRequest, LogsActivity, HasApprovalWorkflow, HasHashid;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'termination_type', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('termination');
    }

    protected $fillable = [
        'employee_id', 'company_id', 'requested_by_user_id', 'termination_type',
        'reason', 'last_working_date', 'effective_date', 'status', 'notes',
        'exit_interview_date', 'exit_interview_notes', 'exit_interview_by_user_id',
        'final_settlement_amount', 'final_settlement_date', 'final_settlement_notes',
    ];

    protected $casts = [
        'last_working_date'       => 'date',
        'effective_date'          => 'date',
        'exit_interview_date'     => 'date',
        'final_settlement_date'   => 'date',
        'final_settlement_amount' => 'integer',
    ];

    public function exitInterviewBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'exit_interview_by_user_id');
    }

    public function hasExitInterview(): bool { return (bool) $this->exit_interview_date; }
    public function hasFinalSettlement(): bool { return (bool) $this->final_settlement_date; }

    public static array $typeLabels = [
        'resign'     => 'Mengundurkan Diri',
        'pkwt_end'   => 'Kontrak Berakhir',
        'dismissal'  => 'PHK',
        'retirement' => 'Pensiun',
        'deceased'   => 'Meninggal Dunia',
        'other'      => 'Lainnya',
    ];

    public function approvalTransactionType(): string
    {
        return 'termination_request';
    }

    public function approvalSummary(): string
    {
        return 'Termination — ' . ($this->employee?->name ?? '?') . ' · '
            . (self::$typeLabels[$this->termination_type] ?? $this->termination_type);
    }

    /** Nonaktifkan karyawan saat disetujui. */
    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();

        $this->employee?->forceFill(['is_active' => false])->save();
    }
}
