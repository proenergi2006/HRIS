<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Histori jabatan/unit INTERNAL (promosi, rotasi, mutasi, pindah antar company
    // dalam grup) — sumber data untuk Career Management.
    public function up(): void
    {
        Schema::create('employee_org_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            // Snapshot teks — supaya histori tetap terbaca meski master berubah.
            $table->string('unit_name', 200)->nullable();
            $table->string('position_name', 150)->nullable();
            $table->enum('change_type', ['join', 'promotion', 'rotation', 'mutation', 'demotion', 'other'])->default('other');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_org_experiences');
    }
};
