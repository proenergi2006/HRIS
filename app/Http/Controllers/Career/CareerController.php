<?php

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Models\CareerPath;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;

/** Career Management — timeline karir karyawan + progress career path (PRD Bab 3 modul #12). */
class CareerController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $companyId = $request->get('company_id', $companies->first()?->id);

        $employees = Employee::with(['position', 'careerPath'])
            ->withCount('orgExperiences')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')->get();

        return view('career.index', compact('employees', 'companies', 'companyId'));
    }

    public function show(Employee $employee)
    {
        $employee->load(['careerPath.steps.position', 'position', 'orgExperiences' => fn ($q) => $q->orderByDesc('start_date')]);
        $careerPaths = CareerPath::where('is_active', true)->orderBy('title')->get();

        // Step career path saat ini (dicocokkan dengan jabatan sekarang) + step berikutnya.
        $currentStep = null;
        $nextStep    = null;
        if ($employee->careerPath) {
            $steps = $employee->careerPath->steps;
            $currentStep = $steps->firstWhere('position_id', $employee->position_id);
            if ($currentStep) {
                $nextStep = $steps->where('step_order', '>', $currentStep->step_order)->sortBy('step_order')->first();
            }
        }

        return view('career.show', compact('employee', 'careerPaths', 'currentStep', 'nextStep'));
    }

    public function assignPath(Request $request, Employee $employee)
    {
        $data = $request->validate(['career_path_id' => 'nullable|exists:career_paths,id']);
        $employee->update($data);

        return back()->with('status', 'Career path karyawan berhasil diperbarui.');
    }
}
