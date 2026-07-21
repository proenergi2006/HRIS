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
        Schema::table('vault_documents', function (Blueprint $table) {
            $table->foreignId('vault_id')->nullable()->after('category_id')
                ->constrained('vaults')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vault_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vault_id');
        });
    }
};
