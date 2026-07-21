<?php

namespace App\Models\GA;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VaultDocument extends Model
{
    use HasHashid;

    protected $fillable = ['category_id', 'vault_id', 'barcode', 'detail', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (self $document) {
            if (! $document->barcode) {
                do {
                    $code = 'DOC-' . strtoupper(Str::random(8));
                } while (static::where('barcode', $code)->exists());
                $document->barcode = $code;
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VaultDocumentCategory::class, 'category_id');
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class, 'vault_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(VaultDocumentTransaction::class, 'document_id');
    }

    public function isOut(): bool
    {
        return $this->transactions()->latest('transaction_date')->latest('id')->first()?->status === 'pengambilan';
    }
}
