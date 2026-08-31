<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentPoolMember extends Model
{
    protected $fillable = [
        'position_id', 'employee_id', 'readiness', 'development_notes', 'added_by_user_id',
    ];

    public static array $readinessLabels = [
        'ready_now'    => 'Siap Sekarang',
        'ready_1_2yr'  => 'Siap 1-2 Tahun',
        'ready_3_5yr'  => 'Siap 3-5 Tahun',
        'development'  => 'Masih Dikembangkan',
    ];

    public static array $readinessBadges = [
        'ready_now'   => 'success',
        'ready_1_2yr' => 'info',
        'ready_3_5yr' => 'warning',
        'development' => 'secondary',
    ];

    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function addedBy(): BelongsTo  { return $this->belongsTo(User::class, 'added_by_user_id'); }
}
