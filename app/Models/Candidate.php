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
        'job_requisition_id', 'name', 'email', 'phone', 'source', 'expected_salary',
        'status', 'mcu_result', 'assessment_result', 'assessment_score', 'assessment_notes',
        'converted_employee_id', 'notes',
    ];

    protected $casts = [
        'expected_salary'  => 'integer',
        'assessment_score' => 'decimal:2',
    ];

    public static array $assessmentLabels = [
        'pass' => 'Lulus',
        'hold' => 'Dipertimbangkan',
        'fail' => 'Tidak Lulus',
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
    public function educations(): HasMany { return $this->hasMany(CandidateEducation::class); }
    public function experiences(): HasMany { return $this->hasMany(CandidateExperience::class)->orderByDesc('start_date'); }
    public function skills(): HasMany { return $this->hasMany(CandidateSkill::class); }
    public function certifications(): HasMany { return $this->hasMany(CandidateCertification::class); }

    public function isConverted(): bool { return $this->status === 'converted'; }
    public function isAccepted(): bool  { return $this->status === 'accepted'; }
}
