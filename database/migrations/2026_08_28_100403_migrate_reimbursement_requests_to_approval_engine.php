<?php

use App\Models\Approval\ApprovalWorkflow;
use App\Models\Reimbursement\ReimbursementRequest;
use App\Services\ApprovalEngine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindahkan modul Medical Reimbursement ke Approval Engine generik.
     * Kolom status sudah varchar — cukup normalisasi nilai.
     */
    public function up(): void
    {
        $inflightIds = DB::table('reimbursement_requests')->where('status', 'submitted')->pluck('id');

        DB::table('reimbursement_requests')->where('status', 'submitted')->update(['status' => 'pending']);

        if (! Schema::hasTable('approval_workflows') || $inflightIds->isEmpty()) {
            return;
        }

        $engine = app(ApprovalEngine::class);

        ReimbursementRequest::whereIn('id', $inflightIds)
            ->whereDoesntHave('approvalRequest')
            ->get()
            ->each(function (ReimbursementRequest $r) use ($engine) {
                $request = $engine->start($r);

                // Alur lama Reimbursement hanya 1 langkah persetujuan (admin).
                // Step direct_manager di alur baru diperlakukan sudah lewat.
                $wf = ApprovalWorkflow::with('steps')
                    ->where('company_id', $request->company_id)
                    ->where('transaction_type', 'reimbursement_request')->first();

                $adminStepOrder = optional(
                    $wf?->steps->first(fn ($s) => $s->approver_role === 'admin' || $s->approver_type === 'specific_role')
                )->step_order ?? 99;

                $request->steps()
                    ->where('status', 'pending')
                    ->where('step_order', '<', $adminStepOrder)
                    ->update([
                        'status'   => 'approved',
                        'acted_at' => now(),
                        'notes'    => 'Disetujui di sistem lama (sebelum migrasi ke engine).',
                    ]);

                $next = $request->steps()->where('status', 'pending')->orderBy('step_order')->first();
                $request->update(['current_step_order' => $next?->step_order ?? $request->steps()->max('step_order')]);

                if (! $next) {
                    $request->update(['status' => 'approved', 'completed_at' => now()]);
                    $r->forceFill(['status' => 'approved'])->save();
                }
            });
    }

    public function down(): void
    {
        DB::table('approval_requests')->where('approvable_type', ReimbursementRequest::class)->delete();
        DB::table('reimbursement_requests')->where('status', 'pending')->update(['status' => 'submitted']);
        DB::table('reimbursement_requests')->where('status', 'cancelled')->update(['status' => 'rejected']);
    }
};
