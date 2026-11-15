<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Formulir Lamaran Kerja publik — posisi yang dilamar (teks bebas, untuk pelamar tanpa requisition). */
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('applied_position', 150)->nullable()->after('job_requisition_id');
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('applied_position');
        });
    }
};
