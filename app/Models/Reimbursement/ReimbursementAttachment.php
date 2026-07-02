<?php

namespace App\Models\Reimbursement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReimbursementAttachment extends Model
{
    protected $fillable = ['reimbursement_request_id', 'file_path', 'file_name', 'doc_type'];

    public static array $docTypes = [
        'invoice'      => 'Invoice / Tagihan RS',
        'kwitansi'     => 'Kwitansi / Struk Pembayaran',
        'resep'        => 'Resep Dokter',
        'surat_dokter' => 'Surat Keterangan Dokter',
        'rujukan'      => 'Surat Rujukan',
        'lab'          => 'Hasil Laboratorium',
        'lainnya'      => 'Dokumen Lainnya',
    ];

    public function getDocTypeLabelAttribute(): string
    {
        return self::$docTypes[$this->doc_type] ?? ($this->doc_type ?? 'Lampiran');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ReimbursementRequest::class, 'reimbursement_request_id');
    }
}
