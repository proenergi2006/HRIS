<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamilyMember extends Model
{
    protected $fillable = ['employee_id', 'relation', 'name', 'birth_date'];

    protected $casts = ['birth_date' => 'date'];

    public static array $relationLabels = [
        'spouse' => 'Istri / Suami',
        'child'  => 'Anak',
    ];

    /** Batas maksimal jumlah anggota keluarga per jenis hubungan, per karyawan. */
    public static array $maxCounts = [
        'spouse' => 1,
        'child'  => 3,
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
