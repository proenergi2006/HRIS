<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Employee Administration — pengajuan perubahan data pribadi (PRD Bab 3 modul #7).
     * Self-service karyawan, lewat Approval Engine — begitu disetujui, field terkait di
     * tabel employees otomatis diperbarui. Field yang boleh diajukan dibatasi whitelist
     * (lihat App\Models\EmployeeDataChangeRequest::$fieldLabels) supaya tidak sembarang
     * kolom sensitif (gaji, jabatan, dst.) bisa diubah lewat jalur self-service ini.
     */
    public function up(): void
    {
        Schema::create('employee_data_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('field_key', 50);
            $table->text('old_value')->nullable();
            $table->text('new_value');
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('draft'); // draft/pending/approved/rejected/cancelled
            $table->text('notes_rejection')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_data_change_requests');
    }
};
