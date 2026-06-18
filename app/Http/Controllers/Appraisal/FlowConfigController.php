<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Appraisal\AppraisalFlowConfig;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Models\Role;

class FlowConfigController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('auth')];
    }

    public function index()
    {
        $configs = AppraisalFlowConfig::orderByRaw('department IS NULL')->orderBy('department')->orderBy('step')->get();

        return view('appraisal.flow-config.index', compact('configs'));
    }

    public function create()
    {
        $roles  = Role::orderBy('name')->pluck('name');
        $config = new AppraisalFlowConfig();

        return view('appraisal.flow-config.edit', compact('config', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $exists = AppraisalFlowConfig::where('department', $data['department'] ?: null)
            ->where('step', $data['step'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['step' => 'Konfigurasi untuk departemen dan step ini sudah ada.'])->withInput();
        }

        AppraisalFlowConfig::create($data);

        return redirect()->route('appraisal.flow-configs.index')
            ->with('status', 'Konfigurasi approval berhasil ditambahkan.');
    }

    public function edit(AppraisalFlowConfig $flowConfig)
    {
        $roles = Role::orderBy('name')->pluck('name');

        return view('appraisal.flow-config.edit', ['config' => $flowConfig, 'roles' => $roles]);
    }

    public function update(Request $request, AppraisalFlowConfig $flowConfig)
    {
        $data = $this->validated($request);

        $duplicate = AppraisalFlowConfig::where('department', $data['department'] ?: null)
            ->where('step', $data['step'])
            ->where('id', '!=', $flowConfig->id)
            ->exists();

        if ($duplicate) {
            return back()->withErrors(['step' => 'Konfigurasi untuk departemen dan step ini sudah ada.'])->withInput();
        }

        $flowConfig->update($data);

        return redirect()->route('appraisal.flow-configs.index')
            ->with('status', 'Konfigurasi approval berhasil diperbarui.');
    }

    public function destroy(AppraisalFlowConfig $flowConfig)
    {
        $flowConfig->delete();

        return redirect()->route('appraisal.flow-configs.index')
            ->with('status', 'Konfigurasi berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'department' => 'nullable|string|max:100',
            'step'       => 'required|integer|in:1,2',
            'role'       => 'required|string|exists:roles,name',
            'label'      => 'required|string|max:100',
        ]);
    }
}
