<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();   // kode bank (mis. 014 BCA) atau slug
            $table->string('name', 150);
            $table->string('swift_code', 20)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now  = now();
        $rows = [
            ['014', 'Bank Central Asia (BCA)', 'CENAIDJA'],
            ['008', 'Bank Mandiri', 'BMRIIDJA'],
            ['002', 'Bank Rakyat Indonesia (BRI)', 'BRINIDJA'],
            ['009', 'Bank Negara Indonesia (BNI)', 'BNINIDJA'],
            ['200', 'Bank Tabungan Negara (BTN)', 'BTANIDJA'],
            ['022', 'CIMB Niaga', 'BNIAIDJA'],
            ['013', 'Bank Permata', 'BBBAIDJA'],
            ['011', 'Bank Danamon', 'BDINIDJA'],
            ['019', 'Bank Panin', 'PINBIDJA'],
            ['016', 'Maybank Indonesia', 'IBBKIDJA'],
            ['451', 'Bank Syariah Indonesia (BSI)', 'BSMDIDJA'],
            ['153', 'Bank Sinarmas', 'SBJKIDJA'],
            ['426', 'Bank Mega', 'MEGAIDJA'],
            ['028', 'Bank OCBC NISP', 'NISPIDJA'],
            ['213', 'Bank BTPN', 'BTPNIDJA'],
        ];

        DB::table('banks')->insert(
            collect($rows)->map(fn ($r, $i) => [
                'code'       => $r[0],
                'name'       => $r[1],
                'swift_code' => $r[2],
                'sort_order' => $i + 1,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
