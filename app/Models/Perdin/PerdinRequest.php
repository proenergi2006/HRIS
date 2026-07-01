<?php

namespace App\Models\Perdin;

use App\Models\User;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PerdinRequest extends Model
{
    use HasHashid, LogsActivity;

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
    public function isSubmitted(): bool        { return $this->status === 'submitted'; }
    public function isReviewedManager(): bool  { return $this->status === 'reviewed_manager'; }
    public function isReviewedHr(): bool       { return $this->status === 'reviewed_hr'; }
    public function isApproved(): bool         { return $this->status === 'approved'; }
    public function isRejected(): bool         { return $this->status === 'rejected'; }
    public function isEditable(): bool         { return in_array($this->status, ['draft', 'rejected']); }

    /**
     * The next approval role required for the current status (null if none).
     */
    public function nextApprovalRole(): ?string
    {
        return match ($this->status) {
            'submitted'        => 'direct_manager',
            'reviewed_manager' => 'hr_manager',
            'reviewed_hr'      => 'ceo',
            default            => null,
        };
    }

    /**
     * Clean round-trip route label, e.g. "Jakarta - Palu - Jakarta".
     * Origin is taken from the requester's department/home base when available,
     * otherwise it falls back to a simple round trip to the destination.
     */
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
        'submitted'        => 'Menunggu Atasan Langsung',
        'reviewed_manager' => 'Menunggu Manager HR & GA',
        'reviewed_hr'      => 'Menunggu Direktur Utama',
        'approved'         => 'Disetujui',
        'rejected'         => 'Ditolak',
    ];

    public static array $statusBadges = [
        'draft'            => 'secondary',
        'submitted'        => 'warning',
        'reviewed_manager' => 'info',
        'reviewed_hr'      => 'primary',
        'approved'         => 'success',
        'rejected'         => 'danger',
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
}
