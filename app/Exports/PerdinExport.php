<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerdinExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $requests) {}

    public function collection(): Collection
    {
        return $this->requests;
    }

    public function headings(): array
    {
        return [
            '#', 'No. Advance', 'Nama Karyawan', 'Departemen', 'Tujuan',
            'Tgl Berangkat', 'Tgl Kembali', 'Keperluan',
            'Total Budget (Rp)', 'Budget Mandiri (Rp)', 'Status',
        ];
    }

    public function map($r): array
    {
        static $i = 0; $i++;
        $status = \App\Models\Perdin\PerdinRequest::$statusLabels[$r->status] ?? $r->status;
        return [
            $i,
            $r->no_advance,
            $r->user?->name ?? '-',
            $r->department ?? '-',
            $r->destination ?? '-',
            $r->departure_date?->format('d/m/Y') ?? '-',
            $r->return_date?->format('d/m/Y') ?? '-',
            $r->purpose ?? '-',
            $r->total_budget ?? 0,
            $r->total_budget_self ?? 0,
            $status,
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
