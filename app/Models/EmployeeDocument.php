<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'doc_type',
        'group',
        'title',
        'file_path',
        'original_name',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    /** Employee Digital File — 5 folder (PRD Bab 5, Modul 7). */
    public static array $groupLabels = [
        'personal'    => 'Personal',
        'employment'  => 'Employment',
        'movement'    => 'Movement',
        'development' => 'Development',
        'exit'        => 'Exit',
        'lainnya'     => 'Lainnya',
    ];

    public static array $docTypes = [
        // Personal
        'KTP'             => 'KTP',
        'KK'              => 'Kartu Keluarga',
        'NPWP'            => 'NPWP',
        'Bank Rekening'   => 'Buku Tabungan / Rekening Bank',
        'BPJS Kesehatan'  => 'BPJS Kesehatan',
        'BPJS TK'         => 'BPJS TK',
        'SIM'             => 'SIM',
        'Paspor'          => 'Paspor',
        'KITAS'           => 'KITAS (Expat)',
        'IMTA'            => 'IMTA / Izin Kerja (Expat)',
        'CV'              => 'CV / Resume',
        // Employment
        'Kontrak Kerja'    => 'Employment Agreement (Kontrak Kerja)',
        'Job Description'  => 'Job Description',
        'Amandemen Kontrak'=> 'Amandemen Kontrak',
        'SK Pengangkatan'  => 'SK Pengangkatan',
        'SK Perpanjangan'  => 'SK Perpanjangan Kontrak',
        // Movement
        'Surat Promosi'    => 'Promotion Letter',
        'Surat Mutasi'     => 'Transfer Letter',
        'Surat Peringatan' => 'Warning Letter',
        // Development
        'Ijazah'               => 'Ijazah',
        'Transkrip'            => 'Transkrip Nilai',
        'Sertifikasi'          => 'Sertifikasi / Lisensi',
        'Sertifikat Training'  => 'Training Certificate',
        'Sertifikat Kompetensi'=> 'Competency Certificate',
        'Hasil Assessment'     => 'Assessment / Hasil Penilaian',
        // Exit
        'Surat Pengunduran Diri' => 'Resignation Letter',
        'Berita Acara Clearance' => 'Clearance (Berita Acara)',
        'Final Settlement'       => 'Final Settlement',
        // Lainnya
        'Lainnya' => 'Lainnya',
    ];

    /** doc_type -> folder (dipakai auto-isi `group` saat unggah). */
    public static array $docGroups = [
        'KTP' => 'personal', 'KK' => 'personal', 'NPWP' => 'personal', 'Bank Rekening' => 'personal',
        'BPJS Kesehatan' => 'personal', 'BPJS TK' => 'personal', 'SIM' => 'personal',
        'Paspor' => 'personal', 'KITAS' => 'personal', 'IMTA' => 'personal', 'CV' => 'personal',
        'Kontrak Kerja' => 'employment', 'Job Description' => 'employment', 'Amandemen Kontrak' => 'employment',
        'SK Pengangkatan' => 'employment', 'SK Perpanjangan' => 'employment',
        'Surat Promosi' => 'movement', 'Surat Mutasi' => 'movement', 'Surat Peringatan' => 'movement',
        'Ijazah' => 'development', 'Transkrip' => 'development', 'Sertifikasi' => 'development',
        'Sertifikat Training' => 'development', 'Sertifikat Kompetensi' => 'development', 'Hasil Assessment' => 'development',
        'Surat Pengunduran Diri' => 'exit', 'Berita Acara Clearance' => 'exit', 'Final Settlement' => 'exit',
        'Lainnya' => 'lainnya',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= 30;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
