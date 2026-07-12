<?php

namespace App\Models\GA;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaultDocumentTransaction extends Model
{
    protected $fillable = [
        'document_id', 'status', 'transaction_date', 'nama',
        'keperluan', 'photo_handover', 'created_by',
    ];
    protected $casts = ['transaction_date' => 'date'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(VaultDocument::class, 'document_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
