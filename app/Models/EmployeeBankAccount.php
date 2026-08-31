<?php

namespace App\Models;

use App\Models\Master\Bank;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBankAccount extends Model
{
    protected $fillable = [
        'employee_id', 'bank_id', 'account_number', 'account_holder_name',
        'branch_name', 'is_primary', 'is_active',
    ];

    protected $casts = [
        'is_primary'     => 'boolean',
        'is_active'      => 'boolean',
        // Data sensitif (PRD Bab 9) — lihat catatan di Employee::$casts.
        'account_number' => 'encrypted',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
