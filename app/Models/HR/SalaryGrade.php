<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Struktur gaji INTERNAL per Level — beda dari SalaryBenchmark (pasar eksternal).
 * `company_id` NULL = berlaku semua PT; diisi = override khusus PT itu (mis. budget
 * beda per anak usaha). Dipakai HR mengontrol ruang kenaikan gaji (merit increase)
 * tanpa harus promosi jabatan/level.
 */
class SalaryGrade extends Model
{
    protected $fillable = [
        'company_id', 'level_id', 'grade_min', 'grade_mid', 'grade_max', 'notes', 'updated_by_user_id',
    ];

    protected $casts = [
        'grade_min' => 'integer',
        'grade_mid' => 'integer',
        'grade_max' => 'integer',
    ];

    public function company(): BelongsTo    { return $this->belongsTo(Company::class); }
    public function level(): BelongsTo      { return $this->belongsTo(Level::class); }
    public function updatedBy(): BelongsTo  { return $this->belongsTo(User::class, 'updated_by_user_id'); }

    /** Posisi gaji dalam band, 0% = di titik min, 100% = di titik maks. Bisa >100% (di atas band). */
    public function positionInBand(int $currentAmount): ?float
    {
        $range = $this->grade_max - $this->grade_min;
        if ($range <= 0) {
            return null;
        }

        return round(($currentAmount - $this->grade_min) / $range * 100, 1);
    }
}
