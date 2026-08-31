<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Job Requisition: kategorikan permintaan (replacement / additional / new_position),
     * tautkan ke Manpower Plan yang disetujui, dan simpan siapa yang digantikan
     * (utk replacement) — dasar Budget Control: permintaan yang menambah headcount
     * (additional/new_position) hanya boleh diajukan bila ada kuota MPP tersisa.
     */
    public function up(): void
    {
        Schema::table('job_requisitions', function (Blueprint $table) {
            $table->string('request_type', 20)->default('replacement')->after('title'); // replacement/additional/new_position
            $table->foreignId('manpower_plan_id')->nullable()->after('request_type')->constrained()->nullOnDelete();
            $table->foreignId('replaces_employee_id')->nullable()->after('manpower_plan_id')->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_requisitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('replaces_employee_id');
            $table->dropConstrainedForeignId('manpower_plan_id');
            $table->dropColumn('request_type');
        });
    }
};
