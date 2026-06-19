<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('driver_name', 100);
            $table->string('driver_phone', 30)->nullable();
            $table->string('destination', 255);
            $table->timestamp('check_in_at');
            $table->timestamp('check_out_at')->nullable();
            $table->unsignedInteger('km_out')->nullable();
            $table->string('photo_dashboard')->nullable();
            $table->string('photo_right')->nullable();
            $table->string('photo_left')->nullable();
            $table->string('photo_front')->nullable();
            $table->string('photo_back')->nullable();
            $table->text('keluhan')->nullable();
            $table->enum('status', ['checked_in', 'checked_out'])->default('checked_in');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_usages');
    }
};
