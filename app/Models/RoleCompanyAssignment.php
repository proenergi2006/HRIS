<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

/**
 * Penetapan "role X berlaku di company mana" untuk seorang user.
 * company_id NULL = role berlaku di semua company.
 */
class RoleCompanyAssignment extends Model
{
    protected $fillable = ['user_id', 'role_id', 'company_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
