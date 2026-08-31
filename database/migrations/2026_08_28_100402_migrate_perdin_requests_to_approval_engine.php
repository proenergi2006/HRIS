<?php

use App\Models\Approval\ApprovalWorkflow;
use App\Models\Perdin\PerdinRequest;
use App\Services\ApprovalEngine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindahkan modul Perjalanan Dinas ke Approval Engine generik.
     * status lama submitted / reviewed_manager / reviewed_hr -> pending.
     * Pengajuan yang masih berjalan didaftarkan ke approval_requests;
     * step yang sudah lewat di sistem lama ditandai approved (berbasis peran).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE perdin_requests MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'draft'");

        $inflight = DB::table('perdin_requests')
            ->whereIn('status', ['submitted', 'reviewed_manager', 'reviewed_hr'])
            ->pluck('status', 'id');

        DB::table('perdin_requests')
            ->whereIn('status', ['submitted', 'reviewed_manager', 'reviewed_hr'])
            ->update(['status' => 'pending']);

        if (! Schema::hasTable('approval_workflows') || $inflight->isEmpty()) {
            return;
        }

        $engine = app(ApprovalEngine::class);

        // Peran approver berikutnya yang masih dibutuhkan berdasarkan status lama.
        $nextRole = [
            'submitted'        => 'direct_manager',
            'reviewed_manager' => 'hr_manager',
            'reviewed_hr'      => 'ceo',
        ];

        PerdinRequest::whereIn('id', $inflight->keys())
            ->whereDoesntHave('approvalRequest')
            ->get()
            ->each(function (PerdinRequest $perdin) use ($engine, $inflight, $nextRole) {
                $request  = $engine->start($perdin);
                $role     = $nextRole[$inflight[$perdin->id]] ?? 'direct_manager';

                // Cari step_order di workflow yg mewakili peran "berikutnya".
                $workflow = ApprovalWorkflow::with('steps')
                    ->where('company_id', $request->company_id)
                    ->where('transaction_type', 'perdin_request')->first();

                $cutoff = optional(
                    $workflow?->steps->first(fn ($s) => $this->matchesRole($s, $role))
                )->step_order ?? 99;

                // Semua step pending SEBELUM cutoff dianggap sudah disetujui di sistem lama.
                $request->steps()
                    ->where('status', 'pending')
                    ->where('step_order', '<', $cutoff)
                    ->update([
                        'status'   => 'approved',
                        'acted_at' => now(),
                        'notes'    => 'Disetujui di sistem lama (sebelum migrasi ke engine).',
                    ]);

                $nextPending = $request->steps()->where('status', 'pending')->orderBy('step_order')->first();

                if ($nextPending) {
                    $request->update(['current_step_order' => $nextPending->step_order]);
                } else {
                    $request->update(['status' => 'approved', 'completed_at' => now()]);
                    $perdin->forceFill(['status' => 'approved'])->save();
                }
            });
    }

    private function matchesRole($workflowStep, string $role): bool
    {
        return match ($role) {
            'direct_manager' => $workflowStep->approver_type === 'direct_manager',
            'hr_manager'     => $workflowStep->approver_role === 'hr_manager'
                || in_array($workflowStep->approver_type, ['department_head', 'section_head', 'division_head']),
            'ceo'            => $workflowStep->approver_role === 'ceo',
            default          => false,
        };
    }

    public function down(): void
    {
        DB::table('approval_requests')
            ->where('approvable_type', PerdinRequest::class)
            ->delete();

        DB::table('perdin_requests')->where('status', 'pending')->update(['status' => 'submitted']);
        DB::table('perdin_requests')->where('status', 'cancelled')->update(['status' => 'rejected']);
        DB::statement("ALTER TABLE perdin_requests MODIFY COLUMN status ENUM('draft','submitted','reviewed_manager','reviewed_hr','approved','rejected') NOT NULL DEFAULT 'draft'");
    }
};
