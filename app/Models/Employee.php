<?php

namespace App\Models;

use App\Models\HR\AttendanceRecord;
use App\Models\HR\EmployeeLoan;
use App\Models\HR\EmployeeSalaryComponent;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveRequest;
use App\Models\Master\BloodType;
use App\Models\Master\City;
use App\Models\Master\EmployeeType;
use App\Models\Master\MaritalStatus;
use App\Models\Master\Province;
use App\Models\Master\Religion;
use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasHashid;

    protected $fillable = [
        'user_id',
        'company_id',
        'branch',
        'branch_id',
        'level_id',
        'career_path_id',
        'manager_id',
        'division_id',
        'department_id',
        'section_id',
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
        'marital_status_id',
        'religion_id',
        'blood_type_id',
        'employee_type',
        'employee_type_id',
        'finger_id',

        // Email & Phone
        'email',
        'phone',
        'home_phone',

        // Alamat Domisili
        'domicile_address',
        'domicile_city',
        'domicile_province_id',
        'domicile_city_id',
        'domicile_district',
        'domicile_district_id',
        'domicile_subdistrict',
        'domicile_village_id',

        // Alamat KTP
        'ktp_address',
        'ktp_city',
        'ktp_province_id',
        'ktp_city_id',
        'ktp_district',
        'ktp_district_id',
        'ktp_subdistrict',
        'ktp_village_id',

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
        // Data sensitif (PRD Bab 9) — dienkripsi transparan lewat cast Laravel.
        // Data lama sudah dienkripsi lewat command employees:encrypt-sensitive
        // (dipanggil dari migration 2026_08_29_140000).
        'ktp_number'        => 'encrypted',
        'npwp_number'       => 'encrypted',
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

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function branchLocation(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    public function bloodType(): BelongsTo
    {
        return $this->belongsTo(BloodType::class);
    }

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class);
    }

    public function domicileProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'domicile_province_id');
    }

    public function domicileCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'domicile_city_id');
    }

    public function ktpProvince(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'ktp_province_id');
    }

    public function ktpCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'ktp_city_id');
    }

    public function domicileDistrict(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\District::class, 'domicile_district_id');
    }

    public function domicileVillage(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Village::class, 'domicile_village_id');
    }

    public function ktpDistrict(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\District::class, 'ktp_district_id');
    }

    public function ktpVillage(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Master\Village::class, 'ktp_village_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function familyMembers(): HasMany
    {
        return $this->hasMany(EmployeeFamilyMember::class);
    }

    public function onboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class);
    }

    public function offboardingTasks(): HasMany
    {
        return $this->hasMany(EmployeeOffboardingTask::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function nssf(): HasOne
    {
        return $this->hasOne(EmployeeNssf::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(EmployeeWorkExperience::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function competencies(): HasMany
    {
        return $this->hasMany(\App\Models\Competency\EmployeeCompetency::class);
    }

    public function orgExperiences(): HasMany
    {
        return $this->hasMany(EmployeeOrgExperience::class);
    }

    public function careerPath(): BelongsTo
    {
        return $this->belongsTo(CareerPath::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(EmployeeFacility::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
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
        return $this->maritalStatus?->name;
    }

    public function getEmployeeTypeLabelAttribute(): string
    {
        return $this->employee_type === 'expat' ? 'Expat' : 'Local';
    }
}
