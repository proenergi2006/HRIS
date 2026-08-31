<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateOffer extends Model
{
    protected $fillable = [
        'candidate_id', 'position_id', 'offered_salary', 'start_date_offered', 'status', 'notes',
    ];

    protected $casts = ['start_date_offered' => 'date'];

    public static array $statusLabels = [
        'draft'    => 'Draft',
        'sent'     => 'Terkirim',
        'accepted' => 'Diterima',
        'declined' => 'Ditolak',
        'expired'  => 'Kedaluwarsa',
    ];

    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
    public function position(): BelongsTo  { return $this->belongsTo(Position::class); }
}
