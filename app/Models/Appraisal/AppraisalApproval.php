<?php

namespace App\Models\Appraisal;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppraisalApproval extends Model
{
    protected $fillable = [
        'appraisal_id',
        'user_id',
        'role',
        'action',
        'status_before',
        'status_after',
        'notes',
    ];

    public function appraisal(): BelongsTo
    {
        return $this->belongsTo(Appraisal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'submit'  => 'Submit',
            'approve' => 'Disetujui',
            'reject'  => 'Dikembalikan',
            default   => $this->action,
        };
    }
}
