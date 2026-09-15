<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeContract extends Model
{
    protected $fillable = [
        'employee_id', 'contract_type', 'number',
        'start_date', 'end_date', 'status',
        'document_path', 'original_name', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public static array $typeLabels = [
        'pkwtt'     => 'PKWTT (Tetap)',
        'pkwt'      => 'PKWT (Kontrak)',
        'probation' => 'Probation',
        'magang'    => 'Magang',
        'harian'    => 'Harian Lepas',
        'mitra'     => 'Mitra',
        'other'     => 'Lainnya',
    ];

    public static array $statusLabels = [
        'active'     => 'Aktif',
        'expired'    => 'Berakhir',
        'terminated' => 'Diputus',
        'renewed'    => 'Diperpanjang',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->contract_type] ?? $this->contract_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }
}
