<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Log riwayat penilaian potensi karyawan (Grid 9-Kotak) — insert-only, tidak pernah diubah. */
class EmployeePotentialHistory extends Model
{
    protected $table = 'employee_potential_history';

    protected $fillable = ['employee_id', 'potential_rating', 'notes', 'assessed_by_user_id', 'assessed_at'];

    protected $casts = ['assessed_at' => 'date'];

    public function employee(): BelongsTo   { return $this->belongsTo(Employee::class); }
    public function assessedBy(): BelongsTo { return $this->belongsTo(User::class, 'assessed_by_user_id'); }
}
