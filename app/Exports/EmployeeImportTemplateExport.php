<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [[
            '1001234', 'Budi Santoso', 'proenergi', 'Keuangan', 'Staff Keuangan', 'Corporate',
            'Staff', '', '2024-01-15', 'Tetap', '', 'Ya',
            'L', 'Jakarta', '1995-05-20', '3171xxxxxxxxxxxx', '09.xxx.xxx.x-xxx.xxx', 'Jakarta', '2020-01-10',
            'Belum Kawin', 'Islam', 'O', 'Local', '',
            'budi@email.com', '08123456789', '',
            'Jl. Contoh No. 1', 'Jakarta', 'Kebayoran Baru', 'Gunung',
            'Jl. Contoh No. 1', 'Jakarta', 'Kebayoran Baru', 'Gunung',
            'Siti Santoso', 'Istri', '08129876543',
        ]];
    }

    public function headings(): array
    {
        return [
            'NIP', 'Nama', 'Kode Perusahaan', 'Departemen', 'Jabatan', 'LOB',
            'Level Jabatan', 'NIP Atasan', 'Tanggal Mulai Kerja', 'Status Kepegawaian', 'Tanggal Kontrak Berakhir', 'Status Aktif',
            'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'No KTP', 'NPWP', 'Kota NPWP', 'Tanggal NPWP',
            'Status Kawin', 'Agama', 'Golongan Darah', 'Tipe Karyawan', 'Finger ID',
            'Email', 'No HP', 'No Telp Rumah',
            'Alamat Domisili', 'Kota Domisili', 'Kecamatan Domisili', 'Kelurahan Domisili',
            'Alamat KTP', 'Kota KTP', 'Kecamatan KTP', 'Kelurahan KTP',
            'Nama Kontak Darurat', 'Hubungan Kontak Darurat', 'No Telp Kontak Darurat',
        ];
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
