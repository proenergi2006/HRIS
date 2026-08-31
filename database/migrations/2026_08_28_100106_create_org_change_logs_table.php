<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit riwayat perubahan struktur organisasi (versioning ringan).
     * Bagan selalu menampilkan kondisi terkini; tabel ini menyimpan jejak
     * "apa yang berubah, oleh siapa, berlaku sejak kapan".
     */
    public function up(): void
    {
        Schema::create('org_change_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('unit_type', ['division', 'department', 'section', 'position']);
            $table->unsignedBigInteger('unit_id');
            $table->string('unit_name', 150)->nullable();
            $table->enum('action', ['created', 'updated', 'moved', 'deactivated', 'deleted']);
            $table->json('changes')->nullable();     // {field: {from, to}}
            $table->date('effective_date');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['unit_type', 'unit_id']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_change_logs');
    }
};
