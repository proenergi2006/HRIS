<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasHashid;

    protected $fillable = [
        'job_requisition_id', 'name', 'email', 'phone', 'source',
        'status', 'mcu_result', 'converted_employee_id', 'notes',
    ];

    public static array $statusLabels = [
        'applied'   => 'Lamaran Masuk',
        'screening' => 'Screening',
        'interview' => 'Interview',
        'offer'     => 'Penawaran',
        'accepted'  => 'Diterima',
        'rejected'  => 'Ditolak',
        'withdrawn' => 'Mengundurkan Diri',
        'converted' => 'Sudah Jadi Karyawan',
    ];

    public static array $statusBadges = [
        'applied'   => 'secondary',
        'screening' => 'info',
        'interview' => 'info',
        'offer'     => 'warning',
        'accepted'  => 'success',
        'rejected'  => 'danger',
        'withdrawn' => 'secondary',
        'converted' => 'primary',
    ];

    /** Urutan tahapan seleksi berikutnya (buat tombol "Lanjut ke tahap berikutnya"). */
    public static array $statusFlow = ['applied', 'screening', 'interview', 'offer', 'accepted'];

    public function jobRequisition(): BelongsTo { return $this->belongsTo(JobRequisition::class); }
    public function convertedEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'converted_employee_id'); }
    public function interviews(): HasMany { return $this->hasMany(CandidateInterview::class)->orderBy('scheduled_at'); }
    public function offers(): HasMany { return $this->hasMany(CandidateOffer::class)->latest(); }
    public function documents(): HasMany { return $this->hasMany(CandidateDocument::class); }

    public function isConverted(): bool { return $this->status === 'converted'; }
    public function isAccepted(): bool  { return $this->status === 'accepted'; }
}
