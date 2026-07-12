<?php

namespace App\Models\GA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaultDocumentCategory extends Model
{
    protected $fillable = ['name', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function documents(): HasMany
    {
        return $this->hasMany(VaultDocument::class, 'category_id');
    }
}
