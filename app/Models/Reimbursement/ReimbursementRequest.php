<?php

namespace App\Models\Reimbursement;

use App\Models\User;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ReimbursementRequest extends Model
{
    use HasHashid, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_claim', 'rejection_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('reimbursement');
    }

    protected $fillable = [
        'user_id', 'request_number', 'request_date', 'medical_for',
        'marital_status', 'status', 'total_claim', 'notes',
        'rejection_reason', 'approved_by', 'approved_at',
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

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isSubmitted(): bool  { return $this->status === 'submitted'; }
    public function isApproved(): bool   { return $this->status === 'approved'; }
    public function isRejected(): bool   { return $this->status === 'rejected'; }

    public static array $medicalForLabels = [
        'employee' => 'Karyawan',
        'spouse'   => 'Istri / Suami',
        'child_1'  => 'Anak ke-1',
        'child_2'  => 'Anak ke-2',
        'child_3'  => 'Anak ke-3',
    ];

    public static array $statusLabels = [
        'draft'     => 'Draft',
        'submitted' => 'Menunggu Approval',
        'approved'  => 'Disetujui',
        'rejected'  => 'Ditolak',
    ];

    public static array $statusBadges = [
        'draft'     => 'secondary',
        'submitted' => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
    ];

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany      { return $this->hasMany(ReimbursementItem::class); }
    public function attachments(): HasMany{ return $this->hasMany(ReimbursementAttachment::class); }
}
