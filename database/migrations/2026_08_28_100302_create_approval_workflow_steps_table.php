<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_workflow_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order');

            // Tipe approver:
            //  direct_manager    — atasan langsung (dinamis dari org chart)
            //  section_head / department_head / division_head — kepala unit
            //  specific_position — jabatan tertentu (approver_position_id)
            //  specific_role     — role sistem tertentu (approver_role, spatie)
            $table->enum('approver_type', [
                'direct_manager', 'section_head', 'department_head',
                'division_head', 'specific_position', 'specific_role',
            ]);
            $table->foreignId('approver_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('approver_role', 50)->nullable();

            // Kondisi opsional — array of {field, operator, value}, semua harus TRUE (AND).
            // Kalau tidak terpenuhi, step di-skip. operator: =, !=, >, >=, <, <=, in
            $table->json('conditions')->nullable();

            // Eskalasi: kalau tidak direspon dalam N hari, dianggap perlu tindak lanjut.
            $table->unsignedSmallInteger('escalate_after_days')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['approval_workflow_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_steps');
    }
};
