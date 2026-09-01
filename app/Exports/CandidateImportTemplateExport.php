<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CandidateImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [[
            'Budi Santoso', 'budi@email.com', '08123456789', 'Jobstreet', '8000000', 'Pengalaman 5 tahun di bidang IT',
        ]];
    }

    public function headings(): array
    {
        return ['Nama', 'Email', 'No HP', 'Sumber', 'Ekspektasi Gaji', 'Catatan'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F2A4A']],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '9CA3AF']],
            ],
        ];
    }
}
