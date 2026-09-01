<?php

namespace App\Imports;

use App\Models\Candidate;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import kandidat massal dari Excel/CSV — dipakai utk memasukkan daftar pelamar
 * yang diseleksi/diekspor dari portal luar (mis. Jobstreet) tanpa ketik ulang
 * satu-satu. Semua baris masuk ke SATU Job Requisition (dipilih di halaman
 * upload), bukan dari kolom di file — file export portal luar tidak akan
 * punya kolom job_requisition_id ProPeople.
 */
class CandidateImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $created = 0;
    public int $skipped = 0;

    /** @var array<int, array{row:int,message:string}> */
    public array $errors = [];

    private int $currentRow = 0;

    public function __construct(private int $jobRequisitionId)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $this->currentRow = $i + 2; // baris 1 = heading

            try {
                $this->importRow($row);
            } catch (\Throwable $e) {
                $this->errors[] = ['row' => $this->currentRow, 'message' => $e->getMessage()];
            }
        }
    }

    private function importRow(Collection $row): void
    {
        $name = $this->str($row['nama'] ?? null);
        if (! $name) {
            throw new \RuntimeException('Kolom Nama wajib diisi.');
        }

        $email = $this->str($row['email'] ?? null);

        // Cegah dobel kalau file yang sama (atau file baru berisi pelamar lama) diupload
        // ulang — dicocokkan lewat email dalam requisition yang sama, kalau email diisi.
        if ($email) {
            $exists = Candidate::where('job_requisition_id', $this->jobRequisitionId)
                ->whereRaw('LOWER(email) = ?', [Str::lower($email)])->exists();
            if ($exists) {
                $this->skipped++;
                return;
            }
        }

        Candidate::create([
            'job_requisition_id' => $this->jobRequisitionId,
            'name'               => $name,
            'email'              => $email,
            'phone'              => $this->str($row['no_hp'] ?? null),
            'source'             => $this->str($row['sumber'] ?? null) ?: 'Jobstreet',
            'expected_salary'    => $this->salary($row['ekspektasi_gaji'] ?? null),
            'notes'              => $this->str($row['catatan'] ?? null),
            'status'             => 'applied',
        ]);

        $this->created++;
    }

    private function str(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function salary(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);

        return $digits === '' ? null : (int) $digits;
    }
}
