<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 3 area "Kematangan HR" lanjutan (setelah Performance & Recruitment):
     * (1) Compensation — benchmark gaji pasar per level (grup, bukan per company — Level
     *     memang entitas global lintas company) + Total Rewards Statement (dihitung dari
     *     data payroll/THR/bonus REAL yang sudah ada, tidak butuh tabel baru untuk itu).
     * (2) Succession Planning — tandai jabatan kritikal + talent pool (kandidat pengganti)
     *     per jabatan dengan tingkat kesiapan.
     * (3) Survey pulse & eNPS — `surveys.type` menandai survey standar/pulse/eNPS (skema
     *     pertanyaan skala 0-10 sudah ada, tinggal ditandai + dihitung skor eNPS-nya).
     */
    public function up(): void
    {
        Schema::create('salary_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->unique()->constrained('levels')->cascadeOnDelete();
            $table->unsignedBigInteger('market_min');
            $table->unsignedBigInteger('market_mid');
            $table->unsignedBigInteger('market_max');
            $table->string('source', 150)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->boolean('is_critical_position')->default(false)->after('tarif_lembur');
            $table->string('succession_risk', 10)->nullable()->after('is_critical_position'); // low/medium/high
            $table->text('succession_notes')->nullable()->after('succession_risk');
        });

        Schema::create('talent_pool_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('readiness', 20); // ready_now/ready_1_2yr/ready_3_5yr/development
            $table->text('development_notes')->nullable();
            $table->foreignId('added_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['position_id', 'employee_id'], 'talent_pool_unique');
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->string('type', 20)->default('standard')->after('is_anonymous'); // standard/pulse/enps
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::dropIfExists('talent_pool_members');
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn(['is_critical_position', 'succession_risk', 'succession_notes']);
        });
        Schema::dropIfExists('salary_benchmarks');
    }
};
