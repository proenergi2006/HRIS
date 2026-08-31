<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Employee Document Management (PRD Bab 5) — kelompokkan employee_documents ke
     * folder Personal/Employment/Movement/Development/Exit (kolom `group`, bukan
     * tabel baru), + perluas katalog doc_type. Expiry alert (documents:remind)
     * SUDAH ADA sejak sebelumnya — tidak berubah.
     */
    public function up(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('group', 20)->default('lainnya')->after('doc_type');
        });

        $map = [
            'KTP' => 'personal', 'KK' => 'personal', 'NPWP' => 'personal',
            'BPJS Kesehatan' => 'personal', 'BPJS TK' => 'personal', 'Bank Rekening' => 'personal',
            'SIM' => 'personal', 'Paspor' => 'personal', 'KITAS' => 'personal', 'IMTA' => 'personal', 'CV' => 'personal',
            'Kontrak Kerja' => 'employment', 'Job Description' => 'employment', 'Amandemen Kontrak' => 'employment',
            'SK Pengangkatan' => 'employment', 'SK Perpanjangan' => 'employment',
            'Surat Promosi' => 'movement', 'Surat Mutasi' => 'movement', 'Surat Peringatan' => 'movement',
            'Ijazah' => 'development', 'Transkrip' => 'development', 'Sertifikasi' => 'development',
            'Sertifikat Training' => 'development', 'Sertifikat Kompetensi' => 'development', 'Hasil Assessment' => 'development',
            'Surat Pengunduran Diri' => 'exit', 'Berita Acara Clearance' => 'exit', 'Final Settlement' => 'exit',
        ];

        foreach ($map as $docType => $group) {
            DB::table('employee_documents')->where('doc_type', $docType)->update(['group' => $group]);
        }
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
