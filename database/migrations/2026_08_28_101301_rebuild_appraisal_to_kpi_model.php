<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance Management rebuild (PRD Bab 3 modul #10) — dari "aspek + bobot"
     * (BS/B/C/K atau 4-penilai self/atasan1/atasan2/HO) ke KPI/objective-based, dan
     * migrasi approval dari state machine 2-step (appraisal_flow_configs) ke
     * Approval Engine generik. 0 appraisal transaksi nyata di DB saat migrasi ini
     * ditulis (cuma data master/config sample) — aman drop total.
     */
    public function up(): void
    {
        Schema::dropIfExists('appraisal_approvals');
        Schema::dropIfExists('appraisal_items');
        Schema::dropIfExists('appraisal_aspect_weights');
        Schema::dropIfExists('appraisal_aspects');
        Schema::dropIfExists('appraisal_flow_configs');

        Schema::table('appraisal_templates', function (Blueprint $table) {
            $table->dropColumn('scoring_type');
        });

        Schema::create('appraisal_template_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_template_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->unsignedTinyInteger('weight_pct')->default(0);
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropColumn([
                'score_self', 'score_atasan1', 'score_atasan2', 'score_ho',
                'avg_late_per_month', 'avg_leave_per_month', 'warning_letter', 'sp_level',
                'decision', 'individual_development_plan',
            ]);
        });

        Schema::table('appraisals', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
            $table->decimal('total_score', 6, 2)->default(0)->change();
            $table->foreignId('appraisal_template_id')->nullable()->change();
            $table->text('development_notes')->nullable()->after('strength_points');
        });

        Schema::table('appraisals', function (Blueprint $table) {
            $table->renameColumn('strength_points', 'strengths');
        });

        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropColumn('development_need');
        });

        Schema::create('appraisal_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->unsignedTinyInteger('weight_pct')->default(0);
            $table->text('target')->nullable();
            $table->text('actual')->nullable();
            $table->decimal('achievement_pct', 6, 2)->nullable();
            $table->decimal('score', 6, 2)->nullable();
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Rebuild penuh — tidak didukung rollback granular, restore dari backup.
    }
};
