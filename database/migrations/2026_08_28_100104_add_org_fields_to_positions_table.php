<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('department_id')
                ->constrained('sections')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->after('section_id')
                ->constrained('levels')->nullOnDelete();
            $table->foreignId('reports_to_position_id')->nullable()->after('level_id')
                ->constrained('positions')->nullOnDelete();
            $table->text('job_description')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('section_id');
            $table->dropConstrainedForeignId('level_id');
            $table->dropConstrainedForeignId('reports_to_position_id');
            $table->dropColumn('job_description');
        });
    }
};
