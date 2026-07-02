<?php

namespace App\Exports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $employees) {}

    public function collection(): Collection
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            '#', 'Nama', 'NIP', 'Departemen', 'LOB', 'Jabatan', 'Level',
            'Status Kepegawaian', 'Tgl Mulai', 'Tgl Kontrak Berakhir',
            'Atasan', 'Status Aktif',
        ];
    }

    public function map($e): array
    {
        static $i = 0; $i++;
        return [
            $i,
            $e->name,
            $e->nip ?? '-',
            $e->department ?? '-',
            $e->lob ?? '-',
            $e->position ?? '-',
            $e->level?->name ?? '-',
            $e->employment_status_label,
            $e->start_date?->format('d/m/Y') ?? '-',
            $e->contract_end_date?->format('d/m/Y') ?? '-',
            $e->manager?->name ?? '-',
            $e->is_active ? 'Aktif' : 'Tidak Aktif',
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
