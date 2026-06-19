<?php

namespace App\Models\GA;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasHashid;

    protected $fillable = ['name', 'plate', 'type', 'color', 'year', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function usages(): HasMany
    {
        return $this->hasMany(VehicleUsage::class);
    }

    public function activeUsage(): HasOne
    {
        return $this->hasOne(VehicleUsage::class)->where('status', 'checked_in')->latestOfMany();
    }

    public function isAvailable(): bool
    {
        return ! $this->usages()->where('status', 'checked_in')->exists();
    }
}
