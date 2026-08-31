<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Enkripsi data sensitif karyawan (NIK, NPWP, No. rekening) yang masih
 * plaintext (PRD Bab 9 — NFR keamanan). Idempotent — value yang sudah
 * terenkripsi (bisa di-decrypt) dilewati, jadi command ini aman dijalankan
 * berulang kali. Dipakai dari migration (up()) DAN bisa dijalankan manual
 * di jalur deploy SQL manual (ALTER TABLE lewat SQL murni, tapi enkripsi
 * datanya wajib lewat command ini karena butuh APP_KEY).
 */
class EncryptSensitiveEmployeeData extends Command
{
    protected $signature   = 'employees:encrypt-sensitive';
    protected $description = 'Enkripsi kolom sensitif karyawan (ktp_number, npwp_number, account_number) yang masih plaintext';

    public function handle(): void
    {
        $this->encryptColumn('employees', 'ktp_number');
        $this->encryptColumn('employees', 'npwp_number');
        $this->encryptColumn('employee_bank_accounts', 'account_number');
    }

    private function encryptColumn(string $table, string $column): void
    {
        $rows = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->select('id', $column)
            ->get();

        $encrypted = 0;
        $skipped   = 0;

        foreach ($rows as $row) {
            $value = $row->$column;

            try {
                Crypt::decryptString($value);
                // Sudah bisa di-decrypt = sudah terenkripsi sebelumnya, lewati.
                $skipped++;
                continue;
            } catch (\Throwable) {
                // Bukan ciphertext valid = masih plaintext, lanjut enkripsi di bawah.
            }

            DB::table($table)->where('id', $row->id)->update([
                $column => Crypt::encryptString($value),
            ]);
            $encrypted++;
        }

        $this->info("{$table}.{$column}: {$encrypted} baris dienkripsi, {$skipped} sudah terenkripsi (dilewati).");
    }
}
