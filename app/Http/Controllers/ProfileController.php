<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'  => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'old_password' => [
                'sometimes', 'nullable',
                'required_with:password',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value && ! Hash::check($value, $user->password)) {
                        $fail('Password lama tidak sesuai.');
                    }
                },
            ],
            'password' => 'sometimes|nullable|required_with:old_password|string|min:6|confirmed',
        ]);

        $user->name  = $request->input('name');
        $user->email = $request->input('email');

        if ($request->input('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui');
    }
}
