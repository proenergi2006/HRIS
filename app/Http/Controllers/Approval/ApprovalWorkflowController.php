<?php

namespace App\Http\Controllers\Approval;

use App\Http\Controllers\Controller;
use App\Models\Approval\ApprovalWorkflow;
use App\Models\Approval\ApprovalWorkflowChangeLog;
use App\Models\Approval\ApprovalWorkflowStep;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Position;
use App\Services\ApprovalEngine;
use App\Traits\LogsApprovalWorkflowChanges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalWorkflowController extends Controller
{
    use LogsApprovalWorkflowChanges;

    public function __construct(private ApprovalEngine $engine) {}

    public function index(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $company   = $request->filled('company')
            ? $companies->firstWhere('id', (int) $request->company)
            : $companies->first();

        $workflows = $company
            ? ApprovalWorkflow::withCount('steps')->where('company_id', $company->id)->get()->keyBy('transaction_type')
            : collect();

        return view('approval.workflow.index', [
            'companies' => $companies,
            'company'   => $company,
            'workflows' => $workflows,
            'types'     => ApprovalWorkflow::$transactionTypes,
        ]);
    }

    public function edit(Company $company, string $type)
    {
        abort_unless(array_key_exists($type, ApprovalWorkflow::$transactionTypes), 404);

        $workflow = ApprovalWorkflow::with('steps.position')
            ->firstOrNew(['company_id' => $company->id, 'transaction_type' => $type]);

        return view('approval.workflow.edit', [
            'company'       => $company,
            'type'          => $type,
            'typeLabel'     => ApprovalWorkflow::$transactionTypes[$type],
            'workflow'      => $workflow,
            'steps'         => $workflow->exists ? $workflow->steps : collect(),
            'approverTypes' => ApprovalWorkflowStep::$approverTypes,
            'positions'     => Position::orderBy('name')->get(['id', 'name']),
            'roles'         => \Spatie\Permission\Models\Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Company $company, string $type)
    {
        abort_unless(array_key_exists($type, ApprovalWorkflow::$transactionTypes), 404);

        $data = $request->validate([
            'steps'                          => 'array',
            'steps.*.approver_type'          => 'required|in:' . implode(',', array_keys(ApprovalWorkflowStep::$approverTypes)),
            'steps.*.approver_position_id'   => 'nullable|exists:positions,id',
            'steps.*.approver_role'          => 'nullable|string|max:50',
            'steps.*.escalate_after_days'    => 'nullable|integer|min:1|max:90',
            'steps.*.condition_field'        => 'nullable|string|max:50',
            'steps.*.condition_operator'     => 'nullable|in:=,!=,>,>=,<,<=,in',
            'steps.*.condition_value'        => 'nullable|string|max:100',
        ]);

        $steps = collect($data['steps'] ?? [])->map(function ($s) {
            $conditions = null;
            if (! empty($s['condition_field']) && isset($s['condition_value'])) {
                $conditions = [[
                    'field'    => $s['condition_field'],
                    'operator' => $s['condition_operator'] ?? '=',
                    'value'    => $s['condition_value'],
                ]];
            }

            return [
                'approver_type'        => $s['approver_type'],
                'approver_position_id' => $s['approver_type'] === 'specific_position' ? ($s['approver_position_id'] ?? null) : null,
                'approver_role'        => $s['approver_type'] === 'specific_role' ? ($s['approver_role'] ?? null) : null,
                'conditions'           => $conditions,
                'escalate_after_days'  => $s['escalate_after_days'] ?? null,
            ];
        })->all();

        $before = $this->snapshotWorkflowSteps($company->id, $type);
        $this->engine->saveWorkflow($company->id, $type, $steps);
        $after = $this->snapshotWorkflowSteps($company->id, $type);

        $this->logWorkflowChange(
            $company->id, $type,
            $before ? 'updated' : 'created',
            $before, $after,
        );

        return redirect()->route('approval.workflows.edit', [$company, $type])
            ->with('success', 'Alur persetujuan disimpan.');
    }

    public function copy(Request $request)
    {
        $data = $request->validate([
            'from_company_id' => 'required|exists:companies,id',
            'to_company_id'   => 'required|different:from_company_id|exists:companies,id',
        ]);

        $source     = ApprovalWorkflow::with('steps')->where('company_id', $data['from_company_id'])->get();
        $fromCompany = Company::find($data['from_company_id']);

        DB::transaction(function () use ($source, $data, $fromCompany) {
            foreach ($source as $wf) {
                $steps = $wf->steps->map(fn ($s) => [
                    'approver_type'        => $s->approver_type,
                    'approver_position_id' => $s->approver_position_id,
                    'approver_role'        => $s->approver_role,
                    'conditions'           => $s->conditions,
                    'escalate_after_days'  => $s->escalate_after_days,
                ])->all();

                $before = $this->snapshotWorkflowSteps($data['to_company_id'], $wf->transaction_type);
                app(ApprovalEngine::class)->saveWorkflow($data['to_company_id'], $wf->transaction_type, $steps, $wf->name);
                $after = $this->snapshotWorkflowSteps($data['to_company_id'], $wf->transaction_type);

                $this->logWorkflowChange(
                    $data['to_company_id'], $wf->transaction_type, 'copied',
                    $before, $after,
                    'Disalin dari ' . ($fromCompany?->name ?? 'perusahaan lain'),
                );
            }
        });

        return back()->with('success', 'Workflow berhasil disalin ke perusahaan tujuan.');
    }

    public function log(Request $request)
    {
        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $logs = ApprovalWorkflowChangeLog::with(['company', 'changedBy'])
            ->when($request->filled('company_id'), fn ($q) => $q->where('company_id', $request->integer('company_id')))
            ->when($request->filled('transaction_type'), fn ($q) => $q->where('transaction_type', $request->transaction_type))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('approval.workflow.log', [
            'logs'      => $logs,
            'companies' => $companies,
            'types'     => ApprovalWorkflow::$transactionTypes,
        ]);
    }

    public function simulate(Request $request, Company $company, string $type)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attributes'  => 'nullable|string',
        ]);

        $employee = Employee::with(['manager', 'section.head', 'department.head', 'division.head'])->find($data['employee_id']);
        $attrs    = $this->parseAttributes($data['attributes'] ?? '');

        $workflow = ApprovalWorkflow::with('steps.position')
            ->where('company_id', $company->id)->where('transaction_type', $type)->first();

        $preview = [];
        foreach ($workflow?->steps ?? [] as $s) {
            $applies = $this->engine->evaluateConditions($s->conditions, $attrs);
            $stub    = new class($employee, $company->id, $type, $attrs) implements \App\Contracts\Approvable {
                public function __construct(public $emp, public $cid, public $type, public $attrs) {}
                public function approvalTransactionType(): string { return $this->type; }
                public function approvalCompanyId(): ?int { return $this->cid; }
                public function approvalRequester(): ?\App\Models\User { return null; }
                public function approvalSubjectEmployee(): ?\App\Models\Employee { return $this->emp; }
                public function approvalAttributes(): array { return $this->attrs; }
                public function approvalSummary(): string { return 'simulasi'; }
                public function onApprovalApproved(): void {}
                public function onApprovalRejected(?string $reason): void {}
            };
            $userId = $applies ? $this->engine->resolveApproverUserId($s, $stub) : null;

            $preview[] = [
                'order'    => $s->step_order,
                'label'    => $s->approver_label,
                'applies'  => $applies,
                'approver' => $userId ? optional(\App\Models\User::find($userId))->name : ($s->approver_type === 'specific_role' ? 'Siapa saja dengan role ' . $s->approver_role : '— tidak ditemukan, step di-skip —'),
            ];
        }

        return back()->with('simulation', ['employee' => $employee->name, 'steps' => $preview]);
    }

    private function parseAttributes(string $raw): array
    {
        $out = [];
        foreach (preg_split('/[\n,]+/', $raw) as $pair) {
            if (str_contains($pair, '=')) {
                [$k, $v] = array_map('trim', explode('=', $pair, 2));
                $out[$k] = is_numeric($v) ? $v + 0 : $v;
            }
        }

        return $out;
    }
}
