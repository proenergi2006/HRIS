<?php

namespace App\Models;

use App\Contracts\Approvable;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeDataChangeRequest extends Model implements Approvable
{
    use HasHashid, LogsActivity, HasApprovalWorkflow;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'field_key', 'old_value', 'new_value', 'notes_rejection'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('employee_data_change');
    }

    protected $fillable = [
        'employee_id', 'requested_by_user_id', 'field_key',
        'old_value', 'new_value', 'reason', 'status', 'notes_rejection',
    ];

    /** Whitelist field yang boleh diajukan lewat self-service — kolom sensitif
     *  (gaji, jabatan, dst.) sengaja TIDAK dimasukkan di sini. */
    public static array $fieldLabels = [
        'phone'                      => 'No. Telepon',
        'email'                      => 'Email',
        'domicile_address'           => 'Alamat Domisili',
        'domicile_district'          => 'Kecamatan (Domisili)',
        'domicile_subdistrict'       => 'Kelurahan (Domisili)',
        'emergency_contact_name'     => 'Nama Kontak Darurat',
        'emergency_contact_relation' => 'Hubungan Kontak Darurat',
        'emergency_contact_phone'    => 'No. Telepon Kontak Darurat',
    ];

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

    public function employee(): BelongsTo    { return $this->belongsTo(Employee::class); }
    public function requestedBy(): BelongsTo { return $this->belongsTo(User::class, 'requested_by_user_id'); }

    public function fieldLabel(): string
    {
        return self::$fieldLabels[$this->field_key] ?? $this->field_key;
    }

    public function isDraft(): bool { return $this->status === 'draft'; }

    // ── Approval Engine ─────────────────────────────────────────────────

    public function approvalTransactionType(): string { return 'employee_data_change_request'; }
    public function approvalCompanyId(): ?int { return $this->employee?->company_id; }
    public function approvalRequester(): ?User { return $this->requestedBy; }
    public function approvalSubjectEmployee(): ?Employee { return $this->employee; }

    public function approvalAttributes(): array
    {
        return ['field_key' => $this->field_key];
    }

    public function approvalSummary(): string
    {
        return 'Perubahan Data — ' . ($this->employee?->name ?? '?') . ' · ' . $this->fieldLabel();
    }

    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();

        if ($this->employee && array_key_exists($this->field_key, self::$fieldLabels)) {
            $this->employee->forceFill([$this->field_key => $this->new_value])->save();
        }
    }

    public function onApprovalRejected(?string $reason): void
    {
        $this->forceFill(['status' => 'rejected', 'notes_rejection' => $reason])->save();
    }
}
