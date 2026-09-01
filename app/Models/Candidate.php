<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidate extends Model
{
    use HasHashid;

    protected $fillable = [
        'job_requisition_id', 'name', 'email', 'phone', 'source', 'expected_salary',
        'status', 'mcu_result', 'assessment_result', 'assessment_score', 'assessment_notes',
        'converted_employee_id', 'notes',
        'referred_by_employee_id', 'referral_bonus_amount', 'referral_bonus_paid_at',
    ];

    protected $casts = [
        'expected_salary'        => 'integer',
        'assessment_score'       => 'decimal:2',
        'referral_bonus_amount'  => 'integer',
        'referral_bonus_paid_at' => 'date',
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
        'accepted'  => 'Hired (Offer Diterima)',
        'rejected'  => 'Ditolak',
        'withdrawn' => 'Mengundurkan Diri',
        'converted' => 'Joined (Sudah Bergabung)',
    ];

    /**
     * Tahap lifecycle ringkas (Candidate → Hired → Pre-Employment → Joined)
     * — turunan dari status + progres checklist pre-employment.
     */
    public function stage(): array
    {
        if ($this->status === 'converted') {
            return ['key' => 'joined', 'label' => 'Joined', 'badge' => 'primary'];
        }
        if (in_array($this->status, ['rejected', 'withdrawn'])) {
            return ['key' => $this->status, 'label' => self::$statusLabels[$this->status], 'badge' => 'danger'];
        }
        if ($this->status === 'accepted') {
            $prog = $this->preEmploymentProgress();
            if ($prog['total'] === 0 || $prog['done'] === 0) {
                return ['key' => 'hired', 'label' => 'Hired', 'badge' => 'success'];
            }

            return $this->preEmploymentComplete()
                ? ['key' => 'preemp_done', 'label' => 'Pre-Employment ✓', 'badge' => 'success']
                : ['key' => 'preemp', 'label' => 'Pre-Employment', 'badge' => 'warning'];
        }

        return ['key' => 'candidate', 'label' => 'Kandidat', 'badge' => 'info'];
    }

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
    public function referredBy(): BelongsTo     { return $this->belongsTo(Employee::class, 'referred_by_employee_id'); }
    public function convertedEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'converted_employee_id'); }
    public function interviews(): HasMany { return $this->hasMany(CandidateInterview::class)->orderBy('scheduled_at'); }
    public function offers(): HasMany { return $this->hasMany(CandidateOffer::class)->latest(); }
    public function documents(): HasMany { return $this->hasMany(CandidateDocument::class); }
    public function educations(): HasMany { return $this->hasMany(CandidateEducation::class); }
    public function experiences(): HasMany { return $this->hasMany(CandidateExperience::class)->orderByDesc('start_date'); }
    public function skills(): HasMany { return $this->hasMany(CandidateSkill::class); }
    public function certifications(): HasMany { return $this->hasMany(CandidateCertification::class); }
    public function preEmployment(): HasOne { return $this->hasOne(CandidatePreEmployment::class); }
    public function preEmploymentTasks(): HasMany { return $this->hasMany(CandidatePreEmploymentTask::class); }

    public function isConverted(): bool { return $this->status === 'converted'; }
    public function isAccepted(): bool  { return $this->status === 'accepted'; }

    /** Item checklist Pre-Employment wajib yang belum diselesaikan. */
    public function preEmploymentMissing(): \Illuminate\Support\Collection
    {
        return $this->preEmploymentTasks
            ->filter(fn ($t) => $t->item?->is_required && ! $t->is_done)
            ->map(fn ($t) => $t->item->label)
            ->values();
    }

    public function preEmploymentComplete(): bool
    {
        return $this->preEmploymentTasks->isNotEmpty() && $this->preEmploymentMissing()->isEmpty();
    }

    public function preEmploymentProgress(): array
    {
        $total = $this->preEmploymentTasks->count();
        $done  = $this->preEmploymentTasks->where('is_done', true)->count();

        return ['done' => $done, 'total' => $total, 'pct' => $total ? (int) round($done / $total * 100) : 0];
    }
}
