<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_template_id')->constrained();
            $table->foreignId('evaluator_id')->constrained('users');

            $table->enum('status', [
                'draft',
                'submitted',
                'approved_user2',
                'approved_cfo',
                'rejected',
            ])->default('draft');

            $table->unsignedInteger('total_score')->default(0);
            $table->string('grade')->nullable();

            // Data absensi (input manual)
            $table->decimal('avg_late_per_month', 5, 2)->default(0);
            $table->decimal('avg_leave_per_month', 5, 2)->default(0);

            // Usulan
            $table->boolean('warning_letter')->default(false);          // Surat Teguran
            $table->enum('sp_level', ['none', 'sp1', 'sp2', 'sp3'])->default('none'); // Surat Peringatan

            // Catatan dan keputusan
            $table->text('notes')->nullable();
            $table->text('decision')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'appraisal_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};
