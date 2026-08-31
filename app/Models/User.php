<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasHashid;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasHashid;

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /** Penetapan role per company (company_id null = role berlaku semua company). */
    public function roleCompanyAssignments(): HasMany
    {
        return $this->hasMany(RoleCompanyAssignment::class);
    }

    /**
     * Daftar id company yang efektif untuk role-assignment user ini. assignment
     * company_id NULL (role lintas-company) = semua company. (Company-switcher di
     * header sudah dihapus karena tidak dipakai modul; helper ini disisakan untuk
     * penentuan scope role bila nanti dibutuhkan.)
     */
    public function accessibleCompanyIds(): array
    {
        $assignments = $this->roleCompanyAssignments()->get();

        if ($assignments->contains(fn ($a) => $a->company_id === null)) {
            return Company::pluck('id')->all();
        }

        return $assignments->pluck('company_id')->filter()->unique()->values()->all();
    }

    /** Apakah user punya role tertentu yang efektif untuk company ini (atau lintas-company). */
    public function hasRoleForCompany(string $roleName, ?int $companyId): bool
    {
        return $this->roleCompanyAssignments()
            ->whereHas('role', fn ($q) => $q->where('name', $roleName))
            ->where(fn ($q) => $q->whereNull('company_id')->when($companyId, fn ($q2) => $q2->orWhere('company_id', $companyId)))
            ->exists();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
