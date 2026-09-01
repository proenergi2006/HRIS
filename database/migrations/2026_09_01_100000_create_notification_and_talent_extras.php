<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 4 gap tambahan hasil review kematangan HR lanjutan:
     * (1) Notification Center — bel notifikasi in-app (Laravel database notifications
     *     standar, dipakai lewat trait Notifiable yang sudah ada di User).
     * (2) Salary Structure internal — band gaji per Level (opsional per company, beda
     *     dari salary_benchmarks yang eksternal/pasar) untuk kontrol kenaikan gaji.
     * (3) 9-box grid — potensi karyawan (potential_rating) ditambah langsung ke
     *     `employees`; sumbu performa dari total_score Appraisal terakhir yang approved
     *     (tidak butuh kolom baru).
     * (4) Recognition / Kudos — apresiasi non-finansial antar karyawan.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('salary_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete(); // NULL = berlaku semua PT
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->unsignedBigInteger('grade_min');
            $table->unsignedBigInteger('grade_mid');
            $table->unsignedBigInteger('grade_max');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('potential_rating', 10)->nullable()->after('is_active'); // low/medium/high
            $table->text('potential_notes')->nullable()->after('potential_rating');
            $table->date('potential_assessed_at')->nullable()->after('potential_notes');
            $table->foreignId('potential_assessed_by_user_id')->nullable()->after('potential_assessed_at')
                ->constrained('users')->nullOnDelete();
        });

        Schema::create('kudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('to_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category', 30); // teamwork/innovation/leadership/customer_focus/integrity/excellence
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kudos');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('potential_assessed_by_user_id');
            $table->dropColumn(['potential_rating', 'potential_notes', 'potential_assessed_at']);
        });
        Schema::dropIfExists('salary_grades');
        Schema::dropIfExists('notifications');
    }
};
