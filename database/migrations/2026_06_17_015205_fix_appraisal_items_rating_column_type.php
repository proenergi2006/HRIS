<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ganti ENUM('BS','B','C','K') ke varchar(5) agar bisa simpan 1-5 juga
        DB::statement("ALTER TABLE appraisal_items MODIFY COLUMN rating VARCHAR(5) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE appraisal_items MODIFY COLUMN rating ENUM('BS','B','C','K') NULL");
    }
};
