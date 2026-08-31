<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\Payroll\TerCategory;

/**
 * PPh21 bulanan metode TER (Tarif Efektif Rata-rata) — PP 58/2023 & PMK 168/2023.
 *
 * ‼️ Tabel tarif (`ter_brackets`, diedit lewat menu Master Data > Tarif PPh21) adalah
 * rekonstruksi Lampiran PMK 168/2023 (31 Agu 2026) — jauh lebih dekat ke aturan resmi
 * dibanding kurva ilustratif versi awal, tapi TETAP belum diverifikasi baris-per-baris
 * ke dokumen PDF resmi (lihat banner di halaman Tarif PPh21 + catatan di
 * database/pph21-ter-rate-correction-manual.sql). Kategori A lebih tinggi keyakinannya
 * daripada B/C. WAJIB dicocokkan Finance/Tax sebelum dipakai penggajian sungguhan.
 * Method ini cuma menghitung PPh21 BULANAN (TER berlaku langsung ke gross bruto tanpa
 * netto biaya jabatan/BPJS — itu memang cara kerja TER, bukan penyederhanaan).
 * Rekonsiliasi tahunan (metode progresif pasal 17 di masa pajak terakhir/Desember)
 * BELUM diimplementasikan — masih perlu dikerjakan manual atau menyusul di iterasi
 * berikutnya.
 */
class Pph21Calculator
{
    /**
     * @return array{amount:int, ptkp_status:string, category:string, rate_percent:float, no_npwp_surcharge:bool}
     */
    public function calculate(Employee $employee, int $taxableGrossMonthly): array
    {
        $ptkpStatus = $this->resolvePtkpStatus($employee);
        $categoryCode = $this->resolveCategoryCode($ptkpStatus);

        $bracket = TerCategory::where('code', $categoryCode)->first()
            ?->brackets()
            ->where('income_from', '<=', $taxableGrossMonthly)
            ->where(function ($q) use ($taxableGrossMonthly) {
                $q->whereNull('income_to')->orWhere('income_to', '>', $taxableGrossMonthly);
            })
            ->first();

        $ratePercent = (float) ($bracket?->rate_percent ?? 0);
        $amount = (int) round($taxableGrossMonthly * $ratePercent / 100);

        // Pasal 21 ayat (5a) UU HPP: tanpa NPWP dikenakan tarif 20% lebih tinggi.
        $noNpwp = empty($employee->npwp_number);
        if ($noNpwp) {
            $amount = (int) round($amount * 1.2);
        }

        return [
            'amount'             => $amount,
            'ptkp_status'        => $ptkpStatus,
            'category'           => $categoryCode,
            'rate_percent'       => $ratePercent,
            'no_npwp_surcharge'  => $noNpwp,
        ];
    }

    /** "TK/0".."TK/3" atau "K/0".."K/3" (tanggungan dibatasi maks 3 sesuai aturan PTKP). */
    public function resolvePtkpStatus(Employee $employee): string
    {
        $base = $employee->maritalStatus?->ptkp_code ?? 'TK';
        $dependents = min(3, $employee->familyMembers()->where('relation', 'child')->count());

        return "{$base}/{$dependents}";
    }

    public function resolveCategoryCode(string $ptkpStatus): string
    {
        return match ($ptkpStatus) {
            'TK/0', 'TK/1', 'K/0' => 'A',
            'TK/2', 'TK/3', 'K/1', 'K/2' => 'B',
            'K/3' => 'C',
            default => 'A',
        };
    }
}
