<?php

namespace App\Models;

use App\Models\HR\AttendanceRecord;
use App\Models\HR\EmployeeSalaryComponent;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveRequest;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasHashid;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch',
        'level_id',
        'manager_id',
        'department_id',
        'position_id',
        'name',
        'photo',
        'nip',
        'lob',
        'start_date',
        'contract_end_date',
        'employment_status',
        'is_active',

        // Personal
        'gender',
        'birth_place',
        'birth_date',
        'ktp_number',
        'npwp_number',
        'npwp_city',
        'npwp_date',
        'marital_status',
        'religion',
        'blood_type',
        'employee_type',
        'finger_id',

        // Email & Phone
        'email',
        'phone',
        'home_phone',

        // Alamat Domisili
        'domicile_address',
        'domicile_city',
        'domicile_district',
        'domicile_subdistrict',

        // Alamat KTP
        'ktp_address',
        'ktp_city',
        'ktp_district',
        'ktp_subdistrict',

        // Kontak Darurat
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'contract_end_date' => 'date',
        'is_active'         => 'boolean',
        'birth_date'        => 'date',
        'npwp_date'         => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal\Appraisal::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function salaryComponents(): HasMany
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        return match($this->employment_status) {
            'permanent'  => 'Tetap',
            'contract'   => 'Kontrak',
            'probation'  => 'Probation',
            default      => $this->employment_status,
        };
    }

    public function getGenderLabelAttribute(): ?string
    {
        return match ($this->gender) {
            'L'     => 'Laki-laki',
            'P'     => 'Perempuan',
            default => null,
        };
    }

    public function getMaritalStatusLabelAttribute(): ?string
    {
        return match ($this->marital_status) {
            'belum_kawin' => 'Belum Kawin',
            'kawin'       => 'Kawin',
            'cerai_hidup' => 'Cerai Hidup',
            'cerai_mati'  => 'Cerai Mati',
            default       => null,
        };
    }

    public function getEmployeeTypeLabelAttribute(): string
    {
        return $this->employee_type === 'expat' ? 'Expat' : 'Local';
    }
}
