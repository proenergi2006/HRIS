<?php

namespace App\Models\Reimbursement;

use App\Contracts\Approvable;
use App\Models\Employee;
use App\Models\User;
use App\Traits\HasApprovalWorkflow;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ReimbursementRequest extends Model implements Approvable
{
    use HasHashid, LogsActivity, HasApprovalWorkflow;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_claim', 'rejection_reason', 'payment_month', 'payment_year'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('reimbursement');
    }

    protected $fillable = [
        'user_id', 'request_number', 'request_date', 'medical_for',
        'marital_status', 'status', 'total_claim', 'notes',
        'rejection_reason', 'approved_by', 'approved_at',
        'payment_month', 'payment_year',
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    public static function generateNumber(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'MED/' . $year . '/' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateTotal(): void
    {
        $this->update(['total_claim' => $this->items()->sum('total_claim')]);
    }

    public function getPaymentPeriodLabelAttribute(): ?string
    {
        if (! $this->payment_month || ! $this->payment_year) return null;
        return \Carbon\Carbon::create($this->payment_year, $this->payment_month)->translatedFormat('F Y');
    }

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isPending(): bool    { return in_array($this->status, ['pending', 'submitted']); }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }

    // Kompatibilitas dgn tampilan lama (mengizinkan koreksi item selama masih pending)
    public function isSubmitted(): bool  { return $this->isPending(); }

    public static array $medicalForLabels = [
        'employee' => 'Karyawan',
        'spouse'   => 'Istri / Suami',
        'child_1'  => 'Anak ke-1',
        'child_2'  => 'Anak ke-2',
        'child_3'  => 'Anak ke-3',
    ];

    public static array $statusLabels = [
        'draft'     => 'Draft',
        'pending'   => 'Menunggu Persetujuan',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'submitted' => 'Menunggu Persetujuan',
    ];

    public static array $statusBadges = [
        'draft'     => 'secondary',
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'cancelled' => 'secondary',
        'submitted' => 'warning',
    ];

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany      { return $this->hasMany(ReimbursementItem::class); }
    public function attachments(): HasMany{ return $this->hasMany(ReimbursementAttachment::class); }

    // ── Approval Engine ─────────────────────────────────────────────────

    public function approvalTransactionType(): string
    {
        return 'reimbursement_request';
    }

    public function approvalCompanyId(): ?int
    {
        return Employee::where('user_id', $this->user_id)->value('company_id');
    }

    public function approvalRequester(): ?User
    {
        return $this->user;
    }

    public function approvalSubjectEmployee(): ?Employee
    {
        return Employee::where('user_id', $this->user_id)->first();
    }

    public function approvalAttributes(): array
    {
        return [
            'total_claim' => (int) $this->total_claim,
            'medical_for' => $this->medical_for,
        ];
    }

    public function approvalSummary(): string
    {
        $for = self::$medicalForLabels[$this->medical_for] ?? $this->medical_for;

        return 'Reimbursement ' . $this->request_number
            . ' — ' . ($this->user?->name ?? '')
            . ' · ' . $for
            . ' · Rp ' . number_format((int) $this->total_claim, 0, ',', '.');
    }

    public function onApprovalApproved(): void
    {
        // Default periode pembayaran ke bulan pengajuan bila belum di-set admin.
        $this->payment_month ??= $this->request_date?->month ?? now()->month;
        $this->payment_year  ??= $this->request_date?->year ?? now()->year;

        $balance = ReimbursementBalance::forUser($this->user_id, $this->request_date->year);
        if ($balance) {
            $balance->increment('used_balance', $this->total_claim);
        }

        $this->forceFill([
            'status'      => 'approved',
            'approved_at' => now(),
        ])->save();
    }

    public function onApprovalRejected(?string $reason): void
    {
        $this->forceFill([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'approved_at'      => now(),
        ])->save();
    }
}
