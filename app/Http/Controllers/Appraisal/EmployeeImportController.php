<?php

namespace App\Http\Controllers\Appraisal;

use App\Exports\EmployeeImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\EmployeeImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeImportController extends Controller
{
    public function form()
    {
        return view('appraisal.employee.import');
    }

    public function template()
    {
        return Excel::download(new EmployeeImportTemplateExport(), 'template-import-karyawan.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new EmployeeImport();
        Excel::import($import, $request->file('file'));

        return redirect()->route('appraisal.employees.import.form')->with('import_result', [
            'created' => $import->created,
            'updated' => $import->updated,
            'errors'  => $import->errors,
        ]);
    }
}
