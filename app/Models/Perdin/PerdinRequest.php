<?php

namespace App\Models\Perdin;

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

class PerdinRequest extends Model implements Approvable
{
    use HasHashid, LogsActivity, HasApprovalWorkflow;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_budget', 'notes_rejection'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('perdin');
    }

    protected $fillable = [
        'no_advance', 'user_id', 'department', 'destination',
        'departure_date', 'departure_time', 'return_date', 'return_time',
        'purpose', 'status', 'total_budget', 'total_budget_self', 'notes_rejection',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date'    => 'date',
    ];

    /**
     * Generate the advance number: SPLIT/YY/MM/XXX
     */
    public static function generateNumber(): string
    {
        $now   = now();
        $yy    = $now->format('y');
        $mm    = $now->format('m');
        $count = static::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count() + 1;

        return 'SPLIT/' . $yy . '/' . $mm . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Recalculate total budget and total self-handled budget from items.
     */
    public function recalculateTotals(): void
    {
        $this->update([
            'total_budget'      => $this->budgetItems()->sum('total_cost'),
            'total_budget_self' => $this->budgetItems()->where('handled_by', 'self')->sum('total_cost'),
        ]);
    }

    public function isDraft(): bool           { return $this->status === 'draft'; }
    public function isPending(): bool         { return in_array($this->status, ['pending', 'submitted', 'reviewed_manager', 'reviewed_hr']); }
    public function isApproved(): bool        { return $this->status === 'approved'; }
    public function isRejected(): bool        { return $this->status === 'rejected'; }
    public function isCancelled(): bool       { return $this->status === 'cancelled'; }
    public function isEditable(): bool        { return in_array($this->status, ['draft', 'rejected']); }

    // Kompatibilitas dgn kode/tampilan lama
    public function isSubmitted(): bool        { return $this->isPending(); }
    public function isReviewedManager(): bool  { return $this->status === 'reviewed_manager'; }
    public function isReviewedHr(): bool       { return $this->status === 'reviewed_hr'; }

    public function nextApprovalRole(): ?string
    {
        return match ($this->status) {
            'submitted'        => 'direct_manager',
            'reviewed_manager' => 'hr_manager',
            'reviewed_hr'      => 'ceo',
            default            => null,
        };
    }

    public function routeLabel(): string
    {
        $origin = config('sipro.company.home_base', 'Jakarta');

        if (! $this->destination) {
            return $origin;
        }

        return $origin . ' - ' . $this->destination . ' - ' . $origin;
    }

    public static array $statusLabels = [
        'draft'            => 'Draft',
        'pending'          => 'Menunggu Persetujuan',
        'approved'         => 'Disetujui',
        'rejected'         => 'Ditolak',
        'cancelled'        => 'Dibatalkan',
        // nilai lama
        'submitted'        => 'Menunggu Persetujuan',
        'reviewed_manager' => 'Menunggu Persetujuan',
        'reviewed_hr'      => 'Menunggu Persetujuan',
    ];

    public static array $statusBadges = [
        'draft'            => 'secondary',
        'pending'          => 'warning',
        'approved'         => 'success',
        'rejected'         => 'danger',
        'cancelled'        => 'secondary',
        'submitted'        => 'warning',
        'reviewed_manager' => 'info',
        'reviewed_hr'      => 'primary',
    ];

    public static array $categoryLabels = [
        'transportasi' => 'Transportasi',
        'penginapan'   => 'Penginapan',
        'lain_lain'    => 'Lain-lain',
        'uang_saku'    => 'Uang Saku',
    ];

    public function user(): BelongsTo        { return $this->belongsTo(User::class); }
    public function budgetItems(): HasMany   { return $this->hasMany(PerdinBudgetItem::class); }
    public function itineraries(): HasMany   { return $this->hasMany(PerdinItinerary::class, 'perdin_request_id')->orderBy('no'); }
    public function approvals(): HasMany     { return $this->hasMany(PerdinApproval::class); }

    // ── Approval Engine ─────────────────────────────────────────────────

    public function approvalTransactionType(): string
    {
        return 'perdin_request';
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
            'total_budget'      => (int) $this->total_budget,
            'total_budget_self' => (int) $this->total_budget_self,
            'department'        => $this->department,
        ];
    }

    public function approvalSummary(): string
    {
        return 'Perdin ' . $this->no_advance
            . ' — ' . ($this->user?->name ?? '')
            . ' → ' . $this->destination
            . ' (' . $this->departure_date?->format('d/m') . '–' . $this->return_date?->format('d/m/Y') . ')'
            . ' · Rp ' . number_format((int) $this->total_budget, 0, ',', '.');
    }

    public function onApprovalApproved(): void
    {
        $this->forceFill(['status' => 'approved'])->save();
    }

    public function onApprovalRejected(?string $reason): void
    {
        $this->forceFill(['status' => 'rejected', 'notes_rejection' => $reason])->save();
    }
}
