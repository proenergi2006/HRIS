<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThrPeriod extends Model
{
    protected $fillable = ['company_id', 'year', 'holiday_name', 'payment_date', 'status', 'closed_by', 'closed_at'];

    protected $casts = ['payment_date' => 'date', 'closed_at' => 'datetime'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function payments(): HasMany  { return $this->hasMany(ThrPayment::class); }

    public function isClosed(): bool { return $this->status === 'closed'; }
}
