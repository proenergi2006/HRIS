<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Log riwayat perubahan band Struktur Gaji Internal — insert-only, tidak pernah diubah. */
class SalaryGradeHistory extends Model
{
    protected $table = 'salary_grade_history';

    protected $fillable = ['company_id', 'level_id', 'grade_min', 'grade_mid', 'grade_max', 'changed_by_user_id'];

    protected $casts = [
        'grade_min' => 'integer', 'grade_mid' => 'integer', 'grade_max' => 'integer',
    ];

    public function company(): BelongsTo   { return $this->belongsTo(Company::class); }
    public function level(): BelongsTo     { return $this->belongsTo(Level::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by_user_id'); }
}
