<?php

namespace App\Models\Survey;

use App\Models\Company;
use App\Models\User;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasHashid;

    protected $fillable = [
        'company_id', 'title', 'description', 'is_anonymous', 'type',
        'status', 'opens_at', 'closes_at', 'created_by_user_id',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'opens_at'     => 'date',
        'closes_at'    => 'date',
    ];

    public static array $typeLabels = [
        'standard' => 'Survey Standar',
        'pulse'    => 'Pulse Survey',
        'enps'     => 'eNPS',
    ];

    public function company(): BelongsTo    { return $this->belongsTo(Company::class); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function questions(): HasMany    { return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order'); }
    public function responses(): HasMany    { return $this->hasMany(SurveyResponse::class); }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
