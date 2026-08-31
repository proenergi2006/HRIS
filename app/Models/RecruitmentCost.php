<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentCost extends Model
{
    protected $fillable = ['company_id', 'job_requisition_id', 'category', 'amount', 'incurred_on', 'notes', 'created_by'];

    protected $casts = ['incurred_on' => 'date'];

    public static array $categoryLabels = [
        'iklan'      => 'Iklan Lowongan',
        'agency'     => 'Agency / Headhunter',
        'assessment' => 'Assessment / Tes',
        'referral'   => 'Referral Bonus',
        'lainnya'    => 'Lainnya',
    ];

    public function company(): BelongsTo        { return $this->belongsTo(Company::class); }
    public function jobRequisition(): BelongsTo { return $this->belongsTo(JobRequisition::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, 'created_by'); }
}
