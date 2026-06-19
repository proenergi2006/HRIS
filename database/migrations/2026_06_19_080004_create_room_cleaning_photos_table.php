<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_cleaning_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_id')->constrained('room_cleaning_log_details')->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_cleaning_photos');
    }
};
