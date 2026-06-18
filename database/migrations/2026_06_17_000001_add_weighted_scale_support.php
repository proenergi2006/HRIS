<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Template: tambah scoring_type
        Schema::table('appraisal_templates', function (Blueprint $table) {
            $table->enum('scoring_type', ['fixed_points', 'weighted_scale'])
                  ->default('fixed_points')
                  ->after('is_default');
        });

        // 2. Aspect: tambah weight_pct (% bobot untuk weighted_scale)
        Schema::table('appraisal_aspects', function (Blueprint $table) {
            $table->unsignedTinyInteger('weight_pct')->nullable()->after('order');
        });

        // 3. Item: tambah evaluator_type (untuk multi-penilai weighted_scale)
        Schema::table('appraisal_items', function (Blueprint $table) {
            $table->enum('evaluator_type', ['evaluator', 'self', 'atasan1', 'atasan2', 'ho'])
                  ->default('evaluator')
                  ->after('score');
            $table->decimal('score', 8, 2)->change(); // allow decimal for weighted_scale
        });

        // 4. Appraisal: tambah skor per penilai + kolom kualitatif
        Schema::table('appraisals', function (Blueprint $table) {
            $table->decimal('score_self',    8, 2)->nullable()->after('total_score');
            $table->decimal('score_atasan1', 8, 2)->nullable()->after('score_self');
            $table->decimal('score_atasan2', 8, 2)->nullable()->after('score_atasan1');
            $table->decimal('score_ho',      8, 2)->nullable()->after('score_atasan2');
            $table->text('strength_points')->nullable()->after('notes');
            $table->text('development_need')->nullable()->after('strength_points');
            $table->text('individual_development_plan')->nullable()->after('development_need');
        });
    }

    public function down(): void
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropColumn(['score_self','score_atasan1','score_atasan2','score_ho',
                                'strength_points','development_need','individual_development_plan']);
        });
        Schema::table('appraisal_items', function (Blueprint $table) {
            $table->dropColumn('evaluator_type');
        });
        Schema::table('appraisal_aspects', function (Blueprint $table) {
            $table->dropColumn('weight_pct');
        });
        Schema::table('appraisal_templates', function (Blueprint $table) {
            $table->dropColumn('scoring_type');
        });
    }
};
