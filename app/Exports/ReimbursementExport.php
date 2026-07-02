<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReimbursementExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $requests) {}

    public function collection(): Collection
    {
        return $this->requests;
    }

    public function headings(): array
    {
        return [
            '#', 'No. Pengajuan', 'Nama Karyawan', 'Tgl Pengajuan',
            'Pengobatan Untuk', 'Status Pernikahan',
            'Total Klaim (Rp)', 'Status', 'Disetujui Oleh', 'Tgl Disetujui', 'Catatan',
        ];
    }

    public function map($r): array
    {
        static $i = 0; $i++;
        $medFor = \App\Models\Reimbursement\ReimbursementRequest::$medicalForLabels[$r->medical_for] ?? $r->medical_for;
        $status = \App\Models\Reimbursement\ReimbursementRequest::$statusLabels[$r->status] ?? $r->status;
        return [
            $i,
            $r->request_number,
            $r->user?->name ?? '-',
            $r->request_date?->format('d/m/Y') ?? '-',
            $medFor,
            $r->marital_status === 'married' ? 'Menikah' : 'Lajang',
            $r->total_claim ?? 0,
            $status,
            $r->approver?->name ?? '-',
            $r->approved_at?->format('d/m/Y H:i') ?? '-',
            $r->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F2A4A']],
            ],
        ];
    }
}
