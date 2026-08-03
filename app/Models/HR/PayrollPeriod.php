<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPeriod extends Model
{
    protected $fillable = ['company_id', 'month', 'year', 'status', 'notes', 'closed_by', 'closed_at'];

    protected $casts = ['closed_at' => 'datetime'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function slips(): HasMany      { return $this->hasMany(PayrollSlip::class); }

    public function getPeriodLabelAttribute(): string
    {
        $months = ['', 'Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'];
        return ($months[$this->month] ?? $this->month) . ' ' . $this->year;
    }

    /**
     * Awal cut-off periode: tanggal 17 bulan sebelumnya.
     */
    public function cutoffStart(): Carbon
    {
        return Carbon::create($this->year, $this->month, 16)->subMonthNoOverflow()->startOfDay()->addDay();
    }

    /**
     * Akhir cut-off periode: tanggal 16 bulan periode ini.
     */
    public function cutoffEnd(): Carbon
    {
        return Carbon::create($this->year, $this->month, 16)->endOfDay();
    }
}
