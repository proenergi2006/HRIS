<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perdin_itinerary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perdin_request_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('no')->default(1);
            $table->date('travel_date');
            $table->time('time_start')->nullable();
            $table->time('time_end')->nullable();
            $table->enum('timezone', ['WIB', 'WITA', 'WIT'])->default('WIB');
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perdin_itinerary');
    }
};
