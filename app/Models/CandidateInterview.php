<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateInterview extends Model
{
    protected $fillable = [
        'candidate_id', 'stage', 'scheduled_at', 'interviewer_employee_id', 'result', 'notes',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public static array $resultLabels = [
        'pending' => 'Menunggu',
        'pass'    => 'Lolos',
        'fail'    => 'Tidak Lolos',
    ];

    public function candidate(): BelongsTo   { return $this->belongsTo(Candidate::class); }
    public function interviewer(): BelongsTo { return $this->belongsTo(Employee::class, 'interviewer_employee_id'); }
}
