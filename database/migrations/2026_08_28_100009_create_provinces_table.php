<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();   // kode wilayah BPS (2 digit)
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Data provinsi + kota/kabupaten di-seed lewat RegionSeeder
        // (database/seeders/RegionSeeder.php) dari database/data/regions.php.
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
