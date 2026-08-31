<?php

namespace App\Models\Approval;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalWorkflow extends Model
{
    protected $fillable = ['company_id', 'transaction_type', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Jenis transaksi yang didukung engine. */
    public static array $transactionTypes = [
        'leave_request'              => 'Pengajuan Cuti',
        'overtime_request'           => 'Pengajuan Lembur',
        'perdin_request'             => 'Perjalanan Dinas',
        'reimbursement_request'      => 'Reimbursement',
        'appraisal'                  => 'Penilaian Kinerja',
        'reward_request'             => 'Reward',
        'punishment_request'         => 'Punishment',
        'promotion_rotation_request' => 'Promosi & Rotasi',
        'termination_request'        => 'Pemutusan Hubungan Kerja',
        'manpower_plan_request'      => 'Manpower Planning',
        'job_requisition'            => 'Job Requisition',
        'employee_data_change_request' => 'Perubahan Data Karyawan',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalWorkflowStep::class)->orderBy('step_order');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::$transactionTypes[$this->transaction_type] ?? $this->transaction_type;
    }
}
