<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_cleaning_log_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained('room_cleaning_logs')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('room_cleaning_items');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_cleaning_log_details');
    }
};
