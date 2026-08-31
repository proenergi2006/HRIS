<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateCertification extends Model
{
    protected $table = 'candidate_certifications';

    protected $fillable = [
        'candidate_id', 'name', 'issuer', 'issued_date', 'expires_date', 'credential_id', 'notes',
    ];

    protected $casts = [
        'issued_date'  => 'date',
        'expires_date' => 'date',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
