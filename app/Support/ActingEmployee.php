<?php

namespace App\Support;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Input ESS "atas nama karyawan": admin HR (punya permission yang diminta)
 * boleh mengajukan lembur/reimbursement untuk karyawan lain lewat
 * parameter `on_behalf_employee_id`. Tanpa itu, target = karyawan milik user sendiri.
 */
class ActingEmployee
{
    public static function canActForOthers(?User $user, string $permission): bool
    {
        return $user !== null && ($user->hasRole('admin') || $user->can($permission));
    }

    /** Karyawan target pengajuan (null bila akun belum terhubung ke data karyawan). */
    public static function resolve(Request $request, string $permission): ?Employee
    {
        $user = $request->user();

        if ($request->filled('on_behalf_employee_id') && self::canActForOthers($user, $permission)) {
            return Employee::where('is_active', true)->find($request->input('on_behalf_employee_id'));
        }

        return $user->employee;
    }

    /** Daftar pilihan karyawan untuk dropdown "Atas nama" (kosong bila tidak berhak). */
    public static function options(?User $user, string $permission)
    {
        if (! self::canActForOthers($user, $permission)) {
            return collect();
        }

        return Employee::where('is_active', true)->orderBy('name')->get(['id', 'name', 'nip']);
    }
}
