<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'doc_type',
        'title',
        'file_path',
        'original_name',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public static array $docTypes = [
        'KTP'           => 'KTP',
        'NPWP'          => 'NPWP',
        'BPJS Kesehatan'=> 'BPJS Kesehatan',
        'BPJS TK'       => 'BPJS TK',
        'Ijazah'        => 'Ijazah',
        'Transkrip'     => 'Transkrip Nilai',
        'SK Pengangkatan'=> 'SK Pengangkatan',
        'SK Perpanjangan'=> 'SK Perpanjangan Kontrak',
        'Sertifikasi'   => 'Sertifikasi / Lisensi',
        'Kontrak Kerja' => 'Kontrak Kerja',
        'CV'            => 'CV / Resume',
        'Lainnya'       => 'Lainnya',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= 30;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
