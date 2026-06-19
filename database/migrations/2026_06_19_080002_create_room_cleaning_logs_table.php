<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_cleaning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('meeting_rooms');
            $table->string('cleaner_name');
            $table->timestamp('cleaned_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_cleaning_logs');
    }
};
