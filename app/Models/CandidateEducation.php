<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateEducation extends Model
{
    protected $table = 'candidate_educations';

    protected $fillable = [
        'candidate_id', 'education_level', 'major', 'institution', 'graduation_year', 'gpa', 'notes',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'gpa'             => 'decimal:2',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
