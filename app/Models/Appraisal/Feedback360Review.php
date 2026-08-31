<?php

namespace App\Models\Appraisal;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Satu baris = 1 rater menilai 1 subjek dalam 1 cycle 360°. */
class Feedback360Review extends Model
{
    protected $table = 'feedback_360_reviews';

    protected $fillable = ['cycle_id', 'subject_employee_id', 'rater_employee_id', 'relation_type', 'status', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    /** Pertanyaan kompetensi tetap — ringkas, cukup di kode (bukan tabel terpisah). */
    public static array $questions = [
        'communication'  => 'Komunikasi — jelas menyampaikan ide & mendengarkan aktif',
        'teamwork'       => 'Kerja Sama Tim — kolaboratif, membantu rekan kerja',
        'quality'        => 'Kualitas Kerja — hasil kerja akurat & rapi',
        'reliability'    => 'Keandalan — bisa diandalkan, memenuhi tenggat waktu',
        'leadership'     => 'Kepemimpinan / Inisiatif — mengambil inisiatif, membimbing orang lain',
        'problem_solving'=> 'Pemecahan Masalah — mencari solusi, adaptif menghadapi kendala',
    ];

    public static array $relationLabels = [
        'self'       => 'Diri Sendiri',
        'manager'    => 'Atasan',
        'peer'       => 'Rekan Kerja',
        'subordinate'=> 'Bawahan',
    ];

    public function cycle(): BelongsTo   { return $this->belongsTo(Feedback360Cycle::class, 'cycle_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Employee::class, 'subject_employee_id'); }
    public function rater(): BelongsTo   { return $this->belongsTo(Employee::class, 'rater_employee_id'); }
    public function answers(): HasMany   { return $this->hasMany(Feedback360Answer::class, 'review_id'); }

    public function isSubmitted(): bool { return $this->status === 'submitted'; }

    public function averageRating(): ?float
    {
        $ratings = $this->answers->pluck('rating')->filter();

        return $ratings->isEmpty() ? null : round($ratings->avg(), 2);
    }
}
