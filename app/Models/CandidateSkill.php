<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSkill extends Model
{
    protected $table = 'candidate_skills';

    protected $fillable = ['candidate_id', 'name', 'proficiency', 'notes'];

    public static array $proficiencyLabels = [
        'basic'        => 'Dasar',
        'intermediate' => 'Menengah',
        'advanced'     => 'Mahir',
        'expert'       => 'Ahli',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
