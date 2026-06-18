<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisal_aspect_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_aspect_id')->constrained()->cascadeOnDelete();
            $table->enum('rating', ['BS', 'B', 'C', 'K']);
            $table->unsignedInteger('score');
            $table->timestamps();

            $table->unique(['appraisal_aspect_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_aspect_weights');
    }
};
