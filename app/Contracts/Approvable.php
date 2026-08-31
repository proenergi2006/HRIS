<?php

namespace App\Contracts;

use App\Models\Employee;
use App\Models\User;

/**
 * Kontrak yang harus dipenuhi setiap transaksi yang dijalankan lewat
 * Approval Engine (App\Services\ApprovalEngine).
 */
interface Approvable
{
    /** Kode jenis transaksi — cocok dengan approval_workflows.transaction_type. */
    public function approvalTransactionType(): string;

    /** Perusahaan pemilik transaksi (menentukan workflow mana yang dipakai). */
    public function approvalCompanyId(): ?int;

    /** User yang mengajukan. */
    public function approvalRequester(): ?User;

    /**
     * Karyawan yang jadi "subjek" transaksi — dipakai untuk resolve
     * atasan langsung / kepala unit dari org chart.
     */
    public function approvalSubjectEmployee(): ?Employee;

    /** Atribut untuk evaluasi kondisi step (mis. ['total_days' => 5]). */
    public function approvalAttributes(): array;

    /** Teks ringkas untuk ditampilkan di inbox approver. */
    public function approvalSummary(): string;

    /** Dipanggil saat semua step selesai disetujui. */
    public function onApprovalApproved(): void;

    /** Dipanggil saat ada step yang menolak. */
    public function onApprovalRejected(?string $reason): void;
}
