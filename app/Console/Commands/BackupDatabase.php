<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Backup database harian otomatis (PRD Bab 9 — NFR). Shell out ke `mysqldump`
 * (bukan package baru — cukup dijadwalkan lewat routes/console.php) dan simpan
 * ke storage/app/backups/, folder yang sama yang sudah dipakai untuk backup
 * manual sesi-sesi sebelumnya. File lebih tua dari $retentionDays dihapus.
 */
class BackupDatabase extends Command
{
    protected $signature   = 'backup:database {--keep-days=14 : Hapus backup otomatis yang lebih tua dari N hari}';
    protected $description = 'Backup database MySQL/MariaDB harian ke storage/app/backups/';

    /** Kandidat lokasi binary mysqldump — coba berurutan sampai ketemu yang bisa dieksekusi. */
    private array $mysqldumpCandidates = [
        'mysqldump',
        '/Applications/XAMPP/xamppfiles/bin/mysqldump',
        '/usr/local/bin/mysqldump',
        '/usr/bin/mysqldump',
        '/opt/homebrew/bin/mysqldump',
    ];

    public function handle(): void
    {
        $connection = config('database.default');
        $config     = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error("Koneksi database default ({$connection}) bukan mysql — backup:database cuma dukung mysql/mariadb.");
            Log::error("backup:database dibatalkan: driver koneksi '{$connection}' bukan mysql.");
            return;
        }

        $binary = $this->resolveMysqldump();
        if (! $binary) {
            $this->error('Binary mysqldump tidak ditemukan di kandidat lokasi manapun.');
            Log::error('backup:database gagal: mysqldump tidak ditemukan.');
            return;
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $filename = 'sipro_auto_' . now()->format('Ymd_His') . '.sql';
        $path     = $dir . DIRECTORY_SEPARATOR . $filename;

        $command = [
            $binary,
            '--host=' . ($config['host'] ?? '127.0.0.1'),
            '--port=' . ($config['port'] ?? 3306),
            '--user=' . ($config['username'] ?? 'root'),
            '--single-transaction',
            '--triggers',
            // --routines sengaja TIDAK dipakai: app ini tidak punya stored
            // procedure/function, dan flag ini butuh baca mysql.proc yang di
            // sebagian environment MariaDB versi campuran bisa error
            // ("Column count of mysql.proc is wrong") walau DB app sendiri sehat.
            $config['database'],
        ];

        $env = [];
        if (! empty($config['password'])) {
            $env['MYSQL_PWD'] = $config['password'];
        }

        $result = Process::env($env)->run($command, function (string $type, string $output) use ($path) {
            File::append($path, $output);
        });

        if (! $result->successful() || ! File::exists($path) || File::size($path) === 0) {
            $this->error('Backup gagal — mysqldump keluar dengan error atau file kosong.');
            Log::error('backup:database gagal: ' . $result->errorOutput());
            File::delete($path);
            return;
        }

        $sizeKb = round(File::size($path) / 1024, 1);
        $this->info("Backup berhasil: {$filename} ({$sizeKb} KB)");

        $this->pruneOldBackups($dir, (int) $this->option('keep-days'));
    }

    private function resolveMysqldump(): ?string
    {
        foreach ($this->mysqldumpCandidates as $bin) {
            $check = Process::run([$bin, '--version']);
            if ($check->successful()) {
                return $bin;
            }
        }

        return null;
    }

    private function pruneOldBackups(string $dir, int $keepDays): void
    {
        $cutoff  = now()->subDays($keepDays);
        $deleted = 0;

        foreach (glob($dir . '/sipro_auto_*.sql') ?: [] as $file) {
            if (File::lastModified($file) < $cutoff->timestamp) {
                File::delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Menghapus {$deleted} backup otomatis yang lebih tua dari {$keepDays} hari.");
        }
    }
}
