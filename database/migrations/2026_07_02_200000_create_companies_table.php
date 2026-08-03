<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();   // proenergi, tds, pfr
            $table->string('name');
            $table->string('short_name', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('npwp', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed 3 perusahaan
        DB::table('companies')->insert([
            ['code' => 'proenergi', 'name' => 'PT. Pro Energi',         'short_name' => 'Pro Energi', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'tds',       'name' => 'PT. Tridaya Selaras',    'short_name' => 'TDS',        'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pfr',       'name' => 'PT. Pinnafore Staraya',  'short_name' => 'PFR',        'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
