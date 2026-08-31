<?php

namespace App\Models\Approval;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris riwayat perubahan alur persetujuan (before/after daftar step). */
class ApprovalWorkflowChangeLog extends Model
{
    protected $fillable = [
        'company_id', 'transaction_type', 'transaction_label', 'action',
        'before', 'after', 'changed_by', 'note',
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Dibuat',
            'updated' => 'Diubah',
            'copied'  => 'Disalin',
            default   => $this->action,
        };
    }
}
