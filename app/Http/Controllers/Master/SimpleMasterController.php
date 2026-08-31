<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Basis untuk master data referensi sederhana yang seragam:
 * satu halaman index + form tambah inline + baris editable
 * (pola sama dengan App\Http\Controllers\Appraisal\DepartmentController).
 *
 * Subclass cukup meng-override properti konfigurasi + extraRules()/extraColumns().
 */
abstract class SimpleMasterController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    protected string $table;
    protected string $routeBase;     // mis. 'master.religions'
    protected string $titlePlural;   // mis. 'Agama'
    protected string $titleSingular; // mis. 'agama'

    /** Kolom ekstra selain code/name yang tampil & bisa diedit di tabel. */
    protected function extraColumns(): array
    {
        return [];
    }

    /** Rule tambahan untuk kolom ekstra. */
    protected function extraRules(): array
    {
        return [];
    }

    /** Relasi yang menghalangi penghapusan (mis. dipakai karyawan). */
    protected function blockingRelations(): array
    {
        return ['employees'];
    }

    public function index()
    {
        $rows = $this->model::query()
            ->when($this->hasSortOrder(), fn ($q) => $q->orderBy('sort_order'))
            ->orderBy('name')
            ->get();

        return view('master.simple', [
            'rows'          => $rows,
            'routeBase'     => $this->routeBase,
            'titlePlural'   => $this->titlePlural,
            'titleSingular' => $this->titleSingular,
            'extraColumns'  => $this->extraColumns(),
            'hasSortOrder'  => $this->hasSortOrder(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] ??= $this->generateCode($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        if ($this->hasSortOrder()) {
            $data['sort_order'] = $request->integer('sort_order') ?: ($this->model::max('sort_order') + 1);
        }

        $this->model::create($data);

        return back()->with('status', $this->titlePlural . ' berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $row  = $this->model::findOrFail($id);
        $data = $this->validated($request, $row->getKey());
        $data['code'] ??= $row->code;
        $data['is_active'] = $request->boolean('is_active', false);
        if ($this->hasSortOrder()) {
            $data['sort_order'] = $request->integer('sort_order');
        }

        $row->update($data);

        return back()->with('status', $this->titlePlural . ' berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $row = $this->model::findOrFail($id);

        foreach ($this->blockingRelations() as $relation) {
            if (method_exists($row, $relation) && $row->{$relation}()->exists()) {
                return back()->with('error', $this->titlePlural . ' tidak bisa dihapus karena masih dipakai.');
            }
        }

        $row->delete();

        return back()->with('status', $this->titlePlural . ' berhasil dihapus.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $rules = [
            'code' => 'nullable|string|max:20|unique:' . $this->table . ',code' . ($ignoreId ? ',' . $ignoreId : ''),
            'name' => 'required|string|max:150',
        ] + $this->extraRules();

        return $request->validate($rules);
    }

    protected function hasSortOrder(): bool
    {
        return in_array('sort_order', (new $this->model)->getFillable(), true);
    }

    protected function generateCode(string $name): string
    {
        $base = Str::upper(Str::slug($name, '_'));
        $base = $base !== '' ? Str::limit($base, 18, '') : 'M';
        $code = $base;
        $i    = 1;

        while ($this->model::where('code', $code)->exists()) {
            $code = Str::limit($base, 16, '') . $i++;
        }

        return $code;
    }
}
