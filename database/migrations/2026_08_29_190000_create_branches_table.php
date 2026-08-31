<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cabang (lokasi kerja) — tingkatan struktur sungguhan di atas Divisi/
     * Departemen di bagan organisasi (Company -> Cabang -> Divisi -> ...).
     * employees.branch (string, default 'HO') TETAP ada untuk kompat mundur;
     * branch_id adalah sumber kebenaran baru utk bagan & master data.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name', 100);
            $table->foreignId('head_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('branch')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
        Schema::dropIfExists('branches');
    }
};
