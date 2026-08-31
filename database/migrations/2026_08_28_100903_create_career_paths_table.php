<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Career Path (PRD Bab 3 modul #12) — template jenjang jabatan berurutan (mis. Staff ->
     *  Senior Staff -> Supervisor -> Manager) yang bisa diassign ke karyawan. */
    public function up(): void
    {
        Schema::create('career_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_paths');
    }
};
