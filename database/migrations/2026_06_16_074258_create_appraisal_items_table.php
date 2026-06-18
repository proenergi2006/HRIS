<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_aspect_id')->constrained()->cascadeOnDelete();
            $table->enum('rating', ['BS', 'B', 'C', 'K'])->nullable();
            $table->unsignedInteger('score')->default(0);
            $table->timestamps();

            $table->unique(['appraisal_id', 'appraisal_aspect_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_items');
    }
};
