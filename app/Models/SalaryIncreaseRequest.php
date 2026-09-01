<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Models\HR\EmployeeSalaryComponent;
use App\Models\HR\SalaryComponent;
use App\Traits\AsApprovableRequest;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** Merit Increase — pengajuan kenaikan gaji lewat Approval Engine. */
class SalaryIncreaseRequest extends Model implements Approvable
{
    use AsApprovableRequest, LogsActivity, HasApprovalWorkflow, HasHashid;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'current_salary', 'proposed_salary', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('salary_increase');
    }

    protected $fillable = [
        'employee_id', 'company_id', 'requested_by_user_id',
        'current_salary', 'proposed_salary', 'effective_date', 'reason', 'status', 'notes',
    ];

    protected $casts = [
        'current_salary'  => 'integer',
        'proposed_salary' => 'integer',
        'effective_date'  => 'date',
    ];

    public function approvalTransactionType(): string
    {
        return 'salary_increase_request';
    }

    public function approvalSummary(): string
    {
        return 'Kenaikan Gaji — ' . ($this->employee?->name ?? '?')
            . ' · Rp ' . number_format((float) $this->current_salary, 0, ',', '.')
            . ' → Rp ' . number_format((float) $this->proposed_salary, 0, ',', '.');
    }

    public function percentIncrease(): ?float
    {
        if (! $this->current_salary) {
            return null;
        }

        return round(($this->proposed_salary - $this->current_salary) / $this->current_salary * 100, 1);
    }

    /** Saat disetujui: update komponen "Gaji Pokok" karyawan ke nominal baru. */
    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();

        $gajiPokok = SalaryComponent::where('name', 'Gaji Pokok')->first();
        if ($gajiPokok && $this->employee_id) {
            EmployeeSalaryComponent::updateOrCreate(
                ['employee_id' => $this->employee_id, 'salary_component_id' => $gajiPokok->id],
                ['amount' => $this->proposed_salary]
            );
        }
    }
}
