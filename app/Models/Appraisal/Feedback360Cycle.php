<?php

namespace App\Models\Appraisal;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feedback360Cycle extends Model
{
    protected $table = 'feedback_360_cycles';

    protected $fillable = ['company_id', 'title', 'period_start', 'period_end', 'status', 'created_by_user_id'];

    protected $casts = ['period_start' => 'date', 'period_end' => 'date'];

    public static array $statusLabels = ['draft' => 'Draft', 'open' => 'Berjalan', 'closed' => 'Selesai'];
    public static array $statusBadges = ['draft' => 'secondary', 'open' => 'warning', 'closed' => 'success'];

    public function company(): BelongsTo   { return $this->belongsTo(Company::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function reviews(): HasMany     { return $this->hasMany(Feedback360Review::class, 'cycle_id'); }
}
