<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_majors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now  = now();
        $rows = [
            'Akuntansi', 'Manajemen', 'Administrasi', 'Ekonomi', 'Hukum',
            'Teknik Informatika', 'Sistem Informasi', 'Teknik Industri',
            'Teknik Mesin', 'Teknik Elektro', 'Teknik Sipil', 'Teknik Kimia',
            'Ilmu Komunikasi', 'Psikologi', 'Sumber Daya Manusia',
            'Perpajakan', 'Sekretari', 'Lainnya',
        ];

        DB::table('education_majors')->insert(
            collect($rows)->map(fn ($name, $i) => [
                'code'       => 'M' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'name'       => $name,
                'sort_order' => $i + 1,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('education_majors');
    }
};
