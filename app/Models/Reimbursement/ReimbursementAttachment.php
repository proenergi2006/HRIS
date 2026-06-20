<?php

namespace App\Models\Reimbursement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReimbursementAttachment extends Model
{
    protected $fillable = ['reimbursement_request_id', 'file_path', 'file_name'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ReimbursementRequest::class, 'reimbursement_request_id');
    }
}
