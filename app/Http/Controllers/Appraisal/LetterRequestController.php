<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\LetterRequest;
use App\Models\LetterTemplate;
use Illuminate\Http\Request;

/**
 * Self-service permintaan surat (Fase 2 HRD) — lightweight, HR-managed langsung
 * (tanpa Approval Engine). Karyawan mengajukan; HR memproses lewat form terbitkan
 * surat existing (EmployeeLetterController), prefilled dari request ini.
 */
class LetterRequestController extends Controller
{
    public function index(Request $request)
    {
        $isHr = $request->user()->can('employee-master.edit');

        $query = LetterRequest::with(['employee', 'requestedBy', 'issuedLetter']);
        if (! $isHr) {
            $employee = $request->user()->employee;
            abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');
            $query->where('employee_id', $employee->id);
        }

        $requests = $query->latest()->get();

        return view('appraisal.employee.letter-request.index', compact('requests', 'isHr'));
    }

    public function create(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $templates = LetterTemplate::where('is_active', true)->where('self_service', true)
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $employee->company_id))
            ->orderBy('title')->get();

        return view('appraisal.employee.letter-request.form', compact('employee', 'templates'));
    }

    public function store(Request $request)
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403, 'Akun Anda belum terhubung ke data karyawan.');

        $data = $request->validate([
            'letter_template_id' => 'required|exists:letter_templates,id',
            'purpose'             => 'required|in:' . implode(',', array_keys(LetterRequest::$purposeLabels)),
            'notes'               => 'nullable|string|max:1000',
        ]);
        $data['employee_id']          = $employee->id;
        $data['requested_by_user_id'] = $request->user()->id;
        $data['status']               = 'pending';

        LetterRequest::create($data);

        return redirect()->route('appraisal.letter-requests.index')
            ->with('status', 'Permintaan surat dikirim ke HR.');
    }

    public function cancel(Request $request, LetterRequest $letterRequest)
    {
        $this->authorizeOwn($request, $letterRequest);
        abort_unless($letterRequest->status === 'pending', 422);

        $letterRequest->update(['status' => 'cancelled']);

        return back()->with('status', 'Permintaan surat dibatalkan.');
    }

    /** HR: arahkan ke form terbitkan surat, prefilled dari request. */
    public function process(Request $request, LetterRequest $letterRequest)
    {
        abort_unless($request->user()->can('employee-master.edit'), 403);
        abort_unless($letterRequest->status === 'pending', 422);

        return redirect()->route('appraisal.employee-letters.create', [
            'employee_id'         => $letterRequest->employee_id,
            'letter_template_id'  => $letterRequest->letter_template_id,
            'letter_request_id'   => $letterRequest->id,
        ]);
    }

    public function reject(Request $request, LetterRequest $letterRequest)
    {
        abort_unless($request->user()->can('employee-master.edit'), 403);
        abort_unless($letterRequest->status === 'pending', 422);

        $data = $request->validate(['rejection_note' => 'nullable|string|max:255']);

        $letterRequest->update([
            'status'          => 'rejected',
            'rejection_note'  => $data['rejection_note'] ?? null,
            'handled_by_user_id' => $request->user()->id,
            'handled_at'      => now(),
        ]);

        return back()->with('status', 'Permintaan surat ditolak.');
    }

    private function authorizeOwn(Request $request, LetterRequest $letterRequest): void
    {
        if ($request->user()->can('employee-master.edit')) {
            return;
        }
        abort_unless($letterRequest->employee_id === $request->user()->employee?->id, 403);
    }
}
