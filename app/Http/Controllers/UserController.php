<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();
        return view('user.index', compact('users'));
    }

    public function create()
    {
        $user      = new User();
        $roles     = Role::orderBy('name')->pluck('name');
        $employees = Employee::orderBy('name')->get();
        return view('user.edit', compact('user', 'roles', 'employees'));
    }

    public function edit(User $user)
    {
        $roles     = Role::orderBy('name')->pluck('name');
        $employees = Employee::orderBy('name')->get();
        return view('user.edit', compact('user', 'roles', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
            'department'  => 'nullable|string|max:100',
            'role'        => 'nullable|string|exists:roles,name',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => bcrypt($request->password),
            'department' => $request->department ?: null,
        ]);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        // Hubungkan ke employee jika dipilih
        if ($request->filled('employee_id')) {
            Employee::where('id', $request->employee_id)->update(['user_id' => $user->id]);
        }

        return redirect()->route('user.index')
            ->with('status', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'department'  => 'nullable|string|max:100',
            'role'        => 'nullable|string|exists:roles,name',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $user->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'department' => $request->department ?: null,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
            $user->update(['password' => bcrypt($request->password)]);
        }

        $user->syncRoles($request->filled('role') ? [$request->role] : []);

        // Update employee link: lepas link lama, set link baru
        Employee::where('user_id', $user->id)->update(['user_id' => null]);
        if ($request->filled('employee_id')) {
            Employee::where('id', $request->employee_id)->update(['user_id' => $user->id]);
        }

        return redirect()->route('user.index')
            ->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return abort(403, 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('user.index')
            ->with('status', 'User berhasil dihapus.');
    }
}
