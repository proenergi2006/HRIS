<?php

namespace App\Models\HR;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = ['company_id', 'name', 'days_per_year', 'is_paid', 'requires_doc', 'is_active'];

    protected $casts = ['is_paid' => 'boolean', 'requires_doc' => 'boolean', 'is_active' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function balances(): HasMany  { return $this->hasMany(LeaveBalance::class); }
    public function requests(): HasMany  { return $this->hasMany(LeaveRequest::class); }
}
