<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kebijakan cuti: kuota per golongan/level + carry-forward/hangus (Fase 2 HRD).
     * LeaveBalance::forEmployee() memakai policy ini (bila ada) sebagai alokasi default,
     * menggantikan LeaveType::days_per_year yang flat untuk semua orang.
     */
    public function up(): void
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete(); // null = semua level
            $table->unsignedTinyInteger('min_years_service')->default(0);
            $table->decimal('quota_days', 5, 1);
            $table->decimal('carry_forward_max_days', 5, 1)->default(0);
            $table->unsignedTinyInteger('carry_forward_expire_month')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['leave_type_id', 'level_id']);
        });

        Schema::table('leave_balances', function (Blueprint $table) {
            $table->decimal('carried_days', 5, 1)->default(0)->after('used');
            $table->date('carried_expires_on')->nullable()->after('carried_days');
        });
    }

    public function down(): void
    {
        Schema::table('leave_balances', function (Blueprint $table) {
            $table->dropColumn(['carried_days', 'carried_expires_on']);
        });
        Schema::dropIfExists('leave_policies');
    }
};
