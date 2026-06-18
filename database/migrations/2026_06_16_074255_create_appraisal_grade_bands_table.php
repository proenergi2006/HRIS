<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisal_grade_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_template_id')->constrained()->cascadeOnDelete();
            $table->string('grade_label');       // "Baik Sekali", "Baik", "Cukup"
            $table->unsignedInteger('min_score'); // skor minimum inklusif
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_grade_bands');
    }
};
