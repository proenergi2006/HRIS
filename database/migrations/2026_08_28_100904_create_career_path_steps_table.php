<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Urutan jabatan dalam 1 career path. */
    public function up(): void
    {
        Schema::create('career_path_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_path_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_path_steps');
    }
};
