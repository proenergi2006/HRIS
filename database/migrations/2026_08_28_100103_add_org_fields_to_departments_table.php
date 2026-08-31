<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('company_id')
                ->constrained('divisions')->nullOnDelete();
            $table->foreignId('head_employee_id')->nullable()->after('name')
                ->constrained('employees')->nullOnDelete();
            $table->string('cost_center', 50)->nullable()->after('head_employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('head_employee_id');
            $table->dropColumn('cost_center');
        });
    }
};
