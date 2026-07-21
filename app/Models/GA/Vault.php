<?php

namespace App\Models\GA;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Vault extends Model
{
    use HasHashid;

    protected $fillable = ['name', 'barcode', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (self $vault) {
            if (! $vault->barcode) {
                do {
                    $code = 'BRK-' . strtoupper(Str::random(8));
                } while (static::where('barcode', $code)->exists());
                $vault->barcode = $code;
            }
        });
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VaultDocument::class, 'vault_id');
    }
}
