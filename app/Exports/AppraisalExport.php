<?php

namespace App\Exports;

use App\Models\Appraisal\Appraisal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AppraisalExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private Collection $appraisals) {}

    public function collection(): Collection
    {
        return $this->appraisals;
    }

    public function headings(): array
    {
        return ['#', 'Nama Karyawan', 'NIP', 'Departemen', 'Jabatan', 'Level', 'Periode', 'Template', 'Total Skor', 'Grade', 'Status', 'Evaluator', 'Tanggal Final'];
    }

    public function map($a): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $a->employee->name,
            $a->employee->nip ?? '-',
            $a->employee->department ?? '-',
            $a->employee->position ?? '-',
            $a->employee->level?->name ?? '-',
            $a->period->name,
            $a->template->name,
            $a->total_score ?: '-',
            $a->grade ?? '-',
            $a->status_label,
            $a->evaluator?->name ?? '-',
            $a->finalized_at?->format('d/m/Y') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F2A4A']], 'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true]],
        ];
    }
}
