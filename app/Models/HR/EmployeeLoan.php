<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'employee_id', 'company_id', 'loan_type', 'reference_no', 'principal',
        'installment_count', 'installment_amount', 'start_month', 'start_year',
        'status', 'notes', 'created_by',
    ];

    public static array $typeLabels = [
        'kasbon'   => 'Kasbon',
        'pinjaman' => 'Pinjaman',
    ];

    public static array $statusLabels = [
        'active'    => 'Aktif',
        'completed' => 'Lunas',
        'cancelled' => 'Dibatalkan',
    ];

    public static array $statusBadges = [
        'active'    => 'warning',
        'completed' => 'success',
        'cancelled' => 'secondary',
    ];

    public function employee(): BelongsTo    { return $this->belongsTo(Employee::class); }
    public function company(): BelongsTo     { return $this->belongsTo(Company::class); }
    public function createdBy(): BelongsTo   { return $this->belongsTo(User::class, 'created_by'); }
    public function installments(): HasMany  { return $this->hasMany(LoanInstallment::class); }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->loan_type] ?? $this->loan_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    /** Sisa cicilan yang belum dipotong (belum deducted / waived). */
    public function outstanding(): int
    {
        return (int) $this->installments()
            ->where('status', 'pending')
            ->sum('amount');
    }

    /** Bentuk jadwal cicilan bulanan dari start_month/start_year. */
    public function generateSchedule(): void
    {
        $this->installments()->delete();

        $month = (int) $this->start_month;
        $year  = (int) $this->start_year;

        for ($i = 0; $i < $this->installment_count; $i++) {
            // Cicilan terakhir menyerap sisa pembulatan.
            $isLast = $i === $this->installment_count - 1;
            $amount = $isLast
                ? $this->principal - ($this->installment_amount * ($this->installment_count - 1))
                : $this->installment_amount;

            $this->installments()->create([
                'period_month' => $month,
                'period_year'  => $year,
                'amount'       => max(0, (int) $amount),
                'status'       => 'pending',
            ]);

            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }
    }
}
