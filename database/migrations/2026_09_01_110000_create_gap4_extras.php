<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 7 gap lanjutan hasil review lebih dalam pasca-4-gap sebelumnya:
     * (1) Notification Center — perluas cakupan pemicu, TIDAK butuh skema baru.
     * (2) Merit Increase — pengajuan kenaikan gaji lewat Approval Engine, mengikuti
     *     pola generik Reward/Punishment/Promosi/Termination (AsApprovableRequest).
     * (3) Employee Referral — kandidat ditandai dirujuk oleh karyawan + bonus referral.
     * (4) Probation Review — evaluasi akhir masa probation, terpisah dari appraisal.
     * (5) Kalender cuti tim — query read-only dari leave_requests, TIDAK butuh skema baru.
     * (6+7) Riwayat Grid 9-Kotak & Struktur Gaji + auto-recurring Pulse Survey.
     */
    public function up(): void
    {
        Schema::create('salary_increase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('current_salary')->nullable();
            $table->unsignedBigInteger('proposed_salary');
            $table->date('effective_date')->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('draft'); // draft/pending/approved/rejected/cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('referred_by_employee_id')->nullable()->after('source')
                ->constrained('employees')->nullOnDelete();
            $table->unsignedBigInteger('referral_bonus_amount')->nullable()->after('referred_by_employee_id');
            $table->date('referral_bonus_paid_at')->nullable()->after('referral_bonus_amount');
        });

        Schema::create('probation_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('review_date');
            $table->string('decision', 20); // passed/extended/failed
            $table->text('performance_notes')->nullable();
            $table->date('extended_until')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_potential_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('potential_rating', 10);
            $table->text('notes')->nullable();
            $table->foreignId('assessed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('assessed_at');
            $table->timestamps();
        });

        Schema::create('salary_grade_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->unsignedBigInteger('grade_min');
            $table->unsignedBigInteger('grade_mid');
            $table->unsignedBigInteger('grade_max');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->string('recurrence', 20)->default('none')->after('type'); // none/monthly/quarterly
            $table->foreignId('parent_survey_id')->nullable()->after('recurrence')
                ->constrained('surveys')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_survey_id');
            $table->dropColumn('recurrence');
        });
        Schema::dropIfExists('salary_grade_history');
        Schema::dropIfExists('employee_potential_history');
        Schema::dropIfExists('probation_reviews');
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_employee_id');
            $table->dropColumn(['referral_bonus_amount', 'referral_bonus_paid_at']);
        });
        Schema::dropIfExists('salary_increase_requests');
    }
};
