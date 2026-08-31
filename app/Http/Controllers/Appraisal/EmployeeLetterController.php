<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLetter;
use App\Models\LetterTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/** Generate & terbitkan surat dari template (PRD Bab 3 modul #7 — "surat"). */
class EmployeeLetterController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeLetter::with(['employee', 'issuedBy']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $letters   = $query->latest('issued_date')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('appraisal.employee.letter.index', compact('letters', 'employees'));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $templates = LetterTemplate::where('is_active', true)->orderBy('title')->get();
        $selectedEmployee = $request->filled('employee_id') ? Employee::find($request->employee_id) : null;
        $letterRequestId  = $request->get('letter_request_id');

        // Datang dari "Proses" permintaan surat karyawan (employee_id + letter_template_id
        // sudah diketahui) -> langsung tampilkan preview tanpa perlu klik Preview lagi.
        $preview = $previewTemplate = null;
        if ($selectedEmployee && $request->filled('letter_template_id')) {
            $previewTemplate = LetterTemplate::find($request->letter_template_id);
            if ($previewTemplate) {
                $preview = $this->merge($previewTemplate->body, $selectedEmployee);
            }
        }

        return view('appraisal.employee.letter.create', compact(
            'employees', 'templates', 'selectedEmployee', 'preview', 'previewTemplate', 'letterRequestId'
        ));
    }

    /** Preview merge hasil template — dipanggil AJAX-less lewat submit form ke halaman yang sama. */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'letter_template_id' => 'required|exists:letter_templates,id',
            'letter_request_id'  => 'nullable|exists:letter_requests,id',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        $template = LetterTemplate::findOrFail($data['letter_template_id']);
        $merged   = $this->merge($template->body, $employee);

        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $templates = LetterTemplate::where('is_active', true)->orderBy('title')->get();

        return view('appraisal.employee.letter.create', [
            'employees'        => $employees,
            'templates'        => $templates,
            'selectedEmployee' => $employee,
            'preview'          => $merged,
            'previewTemplate'  => $template,
            'letterRequestId'  => $data['letter_request_id'] ?? null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'        => 'required|exists:employees,id',
            'letter_template_id' => 'nullable|exists:letter_templates,id',
            'letter_request_id'  => 'nullable|exists:letter_requests,id',
            'title'               => 'required|string|max:200',
            'category'            => 'required|in:' . implode(',', array_keys(\App\Models\LetterTemplate::$categoryLabels)),
            'body'                => 'required|string',
            'issued_date'         => 'required|date',
        ]);

        $letterRequestId = $data['letter_request_id'] ?? null;
        unset($data['letter_request_id']);

        $employee = Employee::findOrFail($data['employee_id']);
        $data['letter_number']     = $this->generateNumber($employee);
        $data['issued_by_user_id'] = auth()->id();
        // Body dikirim dari form SUDAH hasil merge (textarea bisa diedit manual sebelum terbit) —
        // tapi kalau masih ada placeholder tersisa (user tidak preview dulu), merge sekali lagi.
        $data['body'] = $this->merge($data['body'], $employee);

        $letter = EmployeeLetter::create($data);

        // Terbit dari permintaan surat self-service -> tandai selesai & taut ke surat yang diterbitkan.
        if ($letterRequestId) {
            \App\Models\LetterRequest::where('id', $letterRequestId)->update([
                'status'             => 'processed',
                'employee_letter_id' => $letter->id,
                'handled_by_user_id' => auth()->id(),
                'handled_at'         => now(),
            ]);
        }

        return redirect()->route('appraisal.employee-letters.show', $letter)
            ->with('success', 'Surat berhasil diterbitkan.');
    }

    public function show(EmployeeLetter $employeeLetter)
    {
        $employeeLetter->load(['employee', 'issuedBy']);

        return view('appraisal.employee.letter.show', ['letter' => $employeeLetter]);
    }

    public function pdf(EmployeeLetter $employeeLetter)
    {
        $employeeLetter->load(['employee.company']);
        $pdf = Pdf::loadView('appraisal.employee.letter.pdf', ['letter' => $employeeLetter]);

        // letter_number pakai format "0001/SRT/PT/2026" — "/" tidak boleh muncul di
        // nama file (header Content-Disposition), jadi diganti "-" khusus utk filename.
        $safeNumber = str_replace(['/', '\\'], '-', $employeeLetter->letter_number);

        return $pdf->download('surat-' . $safeNumber . '.pdf');
    }

    public function destroy(EmployeeLetter $employeeLetter)
    {
        $employeeLetter->delete();

        return redirect()->route('appraisal.employee-letters.index')->with('success', 'Surat dihapus.');
    }

    private function merge(string $body, Employee $employee): string
    {
        $map = [
            '{{nama}}'        => $employee->name,
            '{{nip}}'         => $employee->nip ?? '-',
            '{{jabatan}}'     => $employee->position?->name ?? '-',
            '{{departemen}}'  => $employee->department?->name ?? '-',
            '{{perusahaan}}'  => $employee->company?->name ?? '-',
            '{{tgl_mulai}}'   => $employee->start_date?->format('d F Y') ?? '-',
            '{{level}}'       => $employee->level?->name ?? '-',
            '{{tanggal}}'     => now()->translatedFormat('d F Y'),
            '{{nomor_surat}}' => $this->generateNumber($employee),
        ];

        return strtr($body, $map);
    }

    private function generateNumber(Employee $employee): string
    {
        $count = EmployeeLetter::whereYear('created_at', now()->year)->count() + 1;

        return str_pad($count, 4, '0', STR_PAD_LEFT) . '/SRT/' . ($employee->company?->code ? strtoupper($employee->company->code) : 'HR') . '/' . now()->format('Y');
    }
}
