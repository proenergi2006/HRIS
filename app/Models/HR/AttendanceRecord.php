<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id', 'company_id', 'date', 'check_in', 'check_out',
        'status', 'late_minutes', 'overtime_minutes', 'source', 'notes',
    ];

    protected $casts = ['date' => 'date'];

    public static array $statusLabels = [
        'hadir'  => 'Hadir',
        'telat'  => 'Hadir (Terlambat)',
        'izin'   => 'Izin',
        'sakit'  => 'Sakit',
        'cuti'   => 'Cuti',
        'alpha'  => 'Alpha',
        'libur'  => 'Libur',
    ];

    public static array $statusBadges = [
        'hadir'  => 'success',
        'telat'  => 'warning',
        'izin'   => 'info',
        'sakit'  => 'info',
        'cuti'   => 'primary',
        'alpha'  => 'danger',
        'libur'  => 'secondary',
    ];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }

    public function getWorkingHours(): ?float
    {
        if (! $this->check_in || ! $this->check_out) return null;
        [$hi, $mi] = explode(':', $this->check_in);
        [$ho, $mo] = explode(':', $this->check_out);
        $diff = ($ho * 60 + $mo) - ($hi * 60 + $mi);
        return round($diff / 60, 1);
    }
}
