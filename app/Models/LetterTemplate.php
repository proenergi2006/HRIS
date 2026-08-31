<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterTemplate extends Model
{
    protected $fillable = ['company_id', 'title', 'category', 'body', 'is_active', 'self_service'];

    protected $casts = ['is_active' => 'boolean', 'self_service' => 'boolean'];

    public static array $categoryLabels = [
        'keterangan_kerja' => 'Surat Keterangan Kerja',
        'peringatan'       => 'Surat Peringatan',
        'mutasi'           => 'Surat Mutasi / Promosi',
        'lainnya'          => 'Lainnya',
    ];

    /** Placeholder yang bisa dipakai di body surat — lihat EmployeeLetterController::merge(). */
    public static array $placeholders = [
        '{{nama}}'        => 'Nama karyawan',
        '{{nip}}'         => 'NIP',
        '{{jabatan}}'     => 'Jabatan',
        '{{departemen}}'  => 'Departemen',
        '{{perusahaan}}'  => 'Nama perusahaan',
        '{{tgl_mulai}}'   => 'Tanggal mulai kerja',
        '{{level}}'       => 'Level/Grade',
        '{{tanggal}}'     => 'Tanggal surat dibuat (hari ini)',
        '{{nomor_surat}}' => 'Nomor surat',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
