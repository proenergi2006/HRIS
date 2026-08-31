<?php

use App\Models\HR\LeaveRequest;
use App\Services\ApprovalEngine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pindahkan modul Cuti ke Approval Engine generik.
     *  - status di-normalisasi: submitted/approved_manager -> pending,
     *    approved_hr -> approved. Kolom manager_ & hr_ lama dibiarkan utk histori.
     *  - pengajuan yang masih berjalan didaftarkan ke approval_requests.
     */
    public function up(): void
    {
        // status: enum -> varchar supaya fleksibel (pending/approved/cancelled).
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'draft'");

        DB::table('leave_requests')->whereIn('status', ['submitted', 'approved_manager'])->update(['status' => 'pending']);
        DB::table('leave_requests')->where('status', 'approved_hr')->update(['status' => 'approved']);

        // Daftarkan pengajuan yang masih pending ke engine (buat approval_request + step).
        if (Schema::hasTable('approval_workflows')) {
            $engine = app(ApprovalEngine::class);

            LeaveRequest::where('status', 'pending')
                ->whereDoesntHave('approvalRequest')
                ->with('employee')
                ->get()
                ->each(function (LeaveRequest $leave) use ($engine) {
                    $engine->start($leave);
                    // start() bisa langsung menyelesaikan bila tidak ada workflow —
                    // sinkronkan status leave-nya.
                    $leave->refresh();
                    if ($leave->approvalRequest?->status === 'approved') {
                        $leave->forceFill(['status' => 'approved'])->save();
                    }
                });
        }
    }

    public function down(): void
    {
        DB::table('leave_requests')->where('status', 'pending')->update(['status' => 'submitted']);
        DB::table('leave_requests')->where('status', 'approved')->update(['status' => 'approved_hr']);
        DB::table('leave_requests')->where('status', 'cancelled')->update(['status' => 'rejected']);

        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('draft','submitted','approved_manager','approved_hr','rejected') NOT NULL DEFAULT 'draft'");
    }
};
