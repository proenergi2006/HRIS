<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perdin_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perdin_request_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['transportasi', 'penginapan', 'lain_lain', 'uang_saku']);
            $table->string('item_name');
            $table->enum('handled_by', ['self', 'ga'])->default('self');
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('unit_cost')->default(0);
            $table->unsignedBigInteger('total_cost')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perdin_budget_items');
    }
};
