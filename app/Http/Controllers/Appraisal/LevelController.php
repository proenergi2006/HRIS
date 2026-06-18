<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LevelController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index()
    {
        $levels = Level::withCount('employees')->get();
        return view('appraisal.level.index', compact('levels'));
    }

    public function create()
    {
        return view('appraisal.level.edit', ['level' => new Level()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:levels,name',
            'description' => 'nullable|string|max:255',
        ]);

        Level::create($request->only('name', 'description'));

        return redirect()->route('appraisal.levels.index')->with('status', 'Level berhasil ditambahkan.');
    }

    public function edit(Level $level)
    {
        return view('appraisal.level.edit', compact('level'));
    }

    public function update(Request $request, Level $level)
    {
        $request->validate([
            'name'        => 'required|string|max:100|unique:levels,name,' . $level->id,
            'description' => 'nullable|string|max:255',
        ]);

        $level->update($request->only('name', 'description'));

        return redirect()->route('appraisal.levels.index')->with('status', 'Level berhasil diperbarui.');
    }

    public function destroy(Level $level)
    {
        if ($level->employees()->count() > 0) {
            return back()->with('error', 'Level tidak bisa dihapus karena masih digunakan oleh karyawan.');
        }

        $level->delete();
        return redirect()->route('appraisal.levels.index')->with('status', 'Level berhasil dihapus.');
    }
}
