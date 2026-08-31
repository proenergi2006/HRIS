<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingParticipant extends Model
{
    protected $fillable = [
        'training_program_id', 'employee_id', 'start_date', 'end_date',
        'status', 'score', 'certificate_number', 'notes',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public static array $statusLabels = [
        'planned'   => 'Direncanakan',
        'ongoing'   => 'Berlangsung',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'no_show'   => 'Tidak Hadir',
    ];

    public static array $statusBadges = [
        'planned'   => 'secondary',
        'ongoing'   => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'no_show'   => 'warning',
    ];

    public function program(): BelongsTo  { return $this->belongsTo(TrainingProgram::class, 'training_program_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
