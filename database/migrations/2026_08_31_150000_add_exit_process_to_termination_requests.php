<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exit Process (PRD Bab 5, folder "Exit") — Exit Interview & Final Settlement,
     * dicatat langsung di termination_requests (1 baris = 1 proses keluar karyawan).
     * Resignation Letter / Berita Acara Clearance tetap file, diunggah lewat vault
     * employee_documents (doc_type Surat Pengunduran Diri / Berita Acara Clearance).
     */
    public function up(): void
    {
        Schema::table('termination_requests', function (Blueprint $table) {
            $table->date('exit_interview_date')->nullable()->after('effective_date');
            $table->text('exit_interview_notes')->nullable()->after('exit_interview_date');
            $table->foreignId('exit_interview_by_user_id')->nullable()->after('exit_interview_notes')
                ->constrained('users')->nullOnDelete();
            $table->bigInteger('final_settlement_amount')->nullable()->after('exit_interview_by_user_id');
            $table->date('final_settlement_date')->nullable()->after('final_settlement_amount');
            $table->text('final_settlement_notes')->nullable()->after('final_settlement_date');
        });
    }

    public function down(): void
    {
        Schema::table('termination_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exit_interview_by_user_id');
            $table->dropColumn([
                'exit_interview_date', 'exit_interview_notes',
                'final_settlement_amount', 'final_settlement_date', 'final_settlement_notes',
            ]);
        });
    }
};
