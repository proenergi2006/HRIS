<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisal_flow_configs', function (Blueprint $table) {
            $table->id();
            // null = default (applies to all departments without a specific config)
            $table->string('department')->nullable();
            $table->unsignedTinyInteger('step');   // 1 = first approver, 2 = final approver
            $table->string('role');                // spatie role name: user_ii, cfo, ceo, …
            $table->string('label');               // display label: "User II", "CFO", "CEO"
            $table->timestamps();

            $table->unique(['department', 'step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisal_flow_configs');
    }
};
