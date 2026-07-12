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
        Schema::create('vault_document_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('vault_documents')->cascadeOnDelete();
            $table->enum('status', ['pengambilan', 'pengembalian']);
            $table->date('transaction_date');
            $table->string('nama', 100);
            $table->enum('keperluan', ['pinjam', 'jual', 'pengembalian_jaminan', 'pengambilan_dokumen']);
            $table->string('photo_handover');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_document_transactions');
    }
};
