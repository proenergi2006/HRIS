<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('nip')->nullable()->unique();
            $table->string('lob')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->date('start_date')->nullable();
            $table->enum('employment_status', ['permanent', 'contract', 'probation'])->default('permanent');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
