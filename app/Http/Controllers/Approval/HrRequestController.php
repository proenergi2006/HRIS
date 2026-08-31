<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Position;
use App\Models\PromotionRotationRequest;
use App\Models\PunishmentRequest;
use App\Models\RewardRequest;
use App\Models\TerminationRequest;
use App\Services\ApprovalEngine;
use Illuminate\Http\Request;

/**
 * Controller generik untuk 4 jenis pengajuan HR baru yang dijalankan
 * lewat Approval Engine: Reward, Punishment, Promosi & Rotasi, Termination.
 */
class HrRequestController extends Controller
{
    public function __construct(private ApprovalEngine $engine) {}

    private const REGISTRY = [
        'reward' => [
            'model'  => RewardRequest::class,
            'label'  => 'Reward',
            'fields' => ['reward_type', 'description', 'effective_date', 'amount'],
            'rules'  => [
                'reward_type'    => 'required|string|max:100',
                'description'    => 'nullable|string',
                'effective_date' => 'nullable|date',
                'amount'         => 'nullable|integer|min:0',
            ],
        ],
        'punishment' => [
            'model'  => PunishmentRequest::class,
            'label'  => 'Punishment',
            'fields' => ['violation_type', 'sanction_level', 'description', 'incident_date', 'effective_date'],
            'rules'  => [
                'violation_type' => 'required|string|max:150',
                'sanction_level' => 'required|in:teguran_lisan,sp1,sp2,sp3,demosi,phk,other',
                'description'    => 'nullable|string',
                'incident_date'  => 'nullable|date',
                'effective_date' => 'nullable|date',
            ],
        ],
        'promotion-rotation' => [
            'model'  => PromotionRotationRequest::class,
            'label'  => 'Promosi & Rotasi',
            'fields' => ['request_type', 'from_position_id', 'to_position_id', 'to_company_id', 'effective_date', 'reason'],
            'rules'  => [
                'request_type'     => 'required|in:promotion,rotation,mutation,demotion',
                'from_position_id' => 'nullable|exists:positions,id',
                'to_position_id'   => 'nullable|exists:positions,id',
                'to_company_id'    => 'nullable|exists:companies,id',
                'effective_date'   => 'nullable|date',
                'reason'           => 'nullable|string',
            ],
        ],
        'termination' => [
            'model'  => TerminationRequest::class,
            'label'  => 'Termination',
            'fields' => ['termination_type', 'reason', 'last_working_date', 'effective_date'],
            'rules'  => [
                'termination_type'  => 'required|in:resign,pkwt_end,dismissal,retirement,deceased,other',
                'reason'            => 'nullable|string',
                'last_working_date' => 'nullable|date',
                'effective_date'    => 'nullable|date',
            ],
        ],
    ];

    private function cfg(string $kind): array
    {
        abort_unless(isset(self::REGISTRY[$kind]), 404);

        return self::REGISTRY[$kind] + ['kind' => $kind];
    }

    public function index(string $kind)
    {
        $cfg   = $this->cfg($kind);
        $model = $cfg['model'];

        $requests = $model::with(['employee', 'approvalRequest'])->latest()->paginate(20);

        return view('approval.hr-request.index', compact('requests', 'cfg', 'kind'));
    }

    public function create(string $kind)
    {
        return view('approval.hr-request.form', $this->formData($kind, new (self::REGISTRY[$kind]['model'])()));
    }

    public function store(Request $request, string $kind)
    {
        $cfg  = $this->cfg($kind);
        $data = $request->validate(['employee_id' => 'required|exists:employees,id'] + $cfg['rules']);

        $employee = Employee::findOrFail($data['employee_id']);
        $model    = $cfg['model'];

        $row = $model::create($data + [
            'company_id'           => $employee->company_id,
            'requested_by_user_id' => auth()->id(),
            'status'               => 'draft',
        ]);

        // Termination -> materialisasi checklist clearance resign supaya HR bisa mulai
        // proses paralel sejak draft, tidak perlu menunggu approval selesai.
        if ($kind === 'termination') {
            \App\Http\Controllers\HR\OffboardingController::materialize($employee);
        }

        return redirect()->route('approval.hr-request.edit', [$kind, $row])
            ->with('success', $cfg['label'] . ' disimpan sebagai draft. Klik "Ajukan" untuk memulai persetujuan.');
    }

    public function edit(string $kind, int $id)
    {
        $row = $this->cfg($kind)['model']::findOrFail($id);

        return view('approval.hr-request.form', $this->formData($kind, $row));
    }

    public function update(Request $request, string $kind, int $id)
    {
        $cfg = $this->cfg($kind);
        $row = $cfg['model']::findOrFail($id);
        abort_unless($row->status === 'draft', 422, 'Pengajuan yang sudah diajukan tidak bisa diubah.');

        $data = $request->validate(['employee_id' => 'required|exists:employees,id'] + $cfg['rules']);
        $row->update($data);

        return back()->with('success', $cfg['label'] . ' diperbarui.');
    }

    public function submit(string $kind, int $id)
    {
        $cfg = $this->cfg($kind);
        $row = $cfg['model']::findOrFail($id);
        abort_unless($row->status === 'draft', 422);

        $row->update(['status' => 'pending']);
        $this->engine->start($row);

        return redirect()->route('approval.hr-request.index', $kind)
            ->with('success', $cfg['label'] . ' diajukan. Menunggu persetujuan.');
    }

    public function show(string $kind, int $id)
    {
        $cfg = $this->cfg($kind);
        $row = $cfg['model']::with(['employee', 'approvalRequest.steps.approver', 'approvalRequest.steps.actedBy'])->findOrFail($id);

        return view('approval.hr-request.show', compact('row', 'cfg', 'kind'));
    }

    public function destroy(string $kind, int $id)
    {
        $cfg = $this->cfg($kind);
        $row = $cfg['model']::findOrFail($id);
        abort_unless(in_array($row->status, ['draft', 'rejected', 'cancelled']), 422);
        $row->delete();

        return redirect()->route('approval.hr-request.index', $kind)->with('success', $cfg['label'] . ' dihapus.');
    }

    private function formData(string $kind, $row): array
    {
        $cfg = $this->cfg($kind);

        return [
            'cfg'       => $cfg,
            'kind'      => $kind,
            'row'       => $row,
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(['id', 'name', 'company_id']),
            'positions' => Position::orderBy('name')->get(['id', 'name']),
            'companies' => Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'short_name']),
            'sanctionLabels' => PunishmentRequest::$sanctionLabels,
            'promoTypes'     => PromotionRotationRequest::$typeLabels,
            'termTypes'      => TerminationRequest::$typeLabels,
        ];
    }
}
