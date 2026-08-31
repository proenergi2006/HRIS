<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\PermissionCatalog;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * "HR bisa bikin role baru sendiri tanpa developer" (PRD Bab 4) — role + matrix
 * permission modul x aksi, admin-only. Pola light-CRUD: 1 index, form tambah inline,
 * baris existing bisa diedit via halaman detail (matrix checkbox terlalu besar utk inline row).
 */
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();
        return view('admin.role.index', compact('roles'));
    }

    public function create()
    {
        $role    = new Role();
        $modules = PermissionCatalog::modules();
        $checked = [];
        return view('admin.role.edit', compact('role', 'modules', 'checked'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60|alpha_dash|unique:roles,name',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($this->permissionsFromRequest($request));

        return redirect()->route('admin.roles.edit', $role)->with('status', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $modules = PermissionCatalog::modules();
        $checked = $role->permissions()->pluck('name')->all();
        return view('admin.role.edit', compact('role', 'modules', 'checked'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60|alpha_dash|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($this->permissionsFromRequest($request));

        return back()->with('status', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'Role tidak bisa dihapus karena masih dipakai user.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Role berhasil dihapus.');
    }

    private function permissionsFromRequest(Request $request): array
    {
        $valid    = PermissionCatalog::allPermissionNames();
        $selected = (array) $request->input('permissions', []);

        return array_values(array_intersect($valid, $selected));
    }
}
