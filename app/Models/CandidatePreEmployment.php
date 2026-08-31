<?php

namespace App\Models;

use App\Models\Master\BloodType;
use App\Models\Master\MaritalStatus;
use App\Models\Master\Religion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePreEmployment extends Model
{
    protected $table = 'candidate_preemployment';

    protected $fillable = [
        'candidate_id',
        'gender', 'birth_place', 'birth_date', 'marital_status_id', 'religion_id', 'blood_type_id',
        'ktp_number', 'npwp_number',
        'ktp_address', 'ktp_city', 'domicile_address', 'domicile_city',
        'bank_id', 'bank_account_number', 'bank_account_holder',
        'bpjs_health_number', 'bpjs_health_date', 'bpjs_employment_number', 'bpjs_employment_date',
        'emergency_contact_name', 'emergency_contact_relation', 'emergency_contact_phone',
        'notes',
    ];

    protected $casts = [
        'birth_date'             => 'date',
        'bpjs_health_date'       => 'date',
        'bpjs_employment_date'   => 'date',
        'ktp_number'             => 'encrypted',
        'npwp_number'            => 'encrypted',
        'bank_account_number'    => 'encrypted',
    ];

    public function candidate(): BelongsTo      { return $this->belongsTo(Candidate::class); }
    public function bank(): BelongsTo           { return $this->belongsTo(\App\Models\Master\Bank::class); }
    public function maritalStatus(): BelongsTo  { return $this->belongsTo(MaritalStatus::class); }
    public function religion(): BelongsTo       { return $this->belongsTo(Religion::class); }
    public function bloodType(): BelongsTo      { return $this->belongsTo(BloodType::class); }
}
