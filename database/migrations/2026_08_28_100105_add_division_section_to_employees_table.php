<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('division_id')->nullable()->after('department_id')
                ->constrained('divisions')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->after('division_id')
                ->constrained('sections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('division_id');
            $table->dropConstrainedForeignId('section_id');
        });
    }
};
