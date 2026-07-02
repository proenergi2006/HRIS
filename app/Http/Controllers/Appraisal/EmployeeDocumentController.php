<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function index(Employee $employee)
    {
        $documents = $employee->documents()->orderBy('doc_type')->orderBy('created_at', 'desc')->get();
        return view('appraisal.employee.documents.index', compact('employee', 'documents'));
    }

    public function create(Employee $employee)
    {
        $docTypes = EmployeeDocument::$docTypes;
        return view('appraisal.employee.documents.create', compact('employee', 'docTypes'));
    }

    public function store(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'doc_type'   => 'required|string|max:60',
            'title'      => 'required|string|max:200',
            'file'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes'      => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date',
        ]);

        $file          = $request->file('file');
        $originalName  = $file->getClientOriginalName();
        $path          = $file->store('employee-documents/' . $employee->id, 'local');

        $employee->documents()->create([
            'doc_type'      => $data['doc_type'],
            'title'         => $data['title'],
            'file_path'     => $path,
            'original_name' => $originalName,
            'notes'         => $data['notes'] ?? null,
            'expires_at'    => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('appraisal.employees.documents.index', $employee)
            ->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(Employee $employee, EmployeeDocument $document)
    {
        abort_if($document->employee_id !== $employee->id, 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function destroy(Employee $employee, EmployeeDocument $document)
    {
        abort_if($document->employee_id !== $employee->id, 404);

        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return redirect()->route('appraisal.employees.documents.index', $employee)
            ->with('success', 'Dokumen berhasil dihapus.');
    }
}
