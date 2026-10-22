<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Models\HR\AttendanceRecord;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * DATA CONTOH untuk melihat tampilan profil, riwayat, kalender absensi & lembur.
 * Hanya mengisi kolom yang masih KOSONG dan tabel yang belum berisi — aman diulang.
 * Jalankan: php artisan db:seed --class=DummyProfileDataSeeder
 */
class DummyProfileDataSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('is_active', true)->orderBy('id')->get();

        foreach ($employees as $i => $e) {
            $this->fillProfile($e, $i);
            $this->seedHistories($e, $i);
            $this->seedAttendance($e);
            $this->seedLeave($e, $i);
            $this->seedReimbursement($e, $i);
        }

        $this->seedOvertime($employees);
    }

    /** Pengajuan cuti/izin dengan status bervariasi (+ saldo bila belum ada). */
    private function seedLeave(Employee $e, int $i): void
    {
        if (\App\Models\HR\LeaveRequest::where('employee_id', $e->id)->exists()) {
            return;
        }

        $rows = [
            [1, now()->subMonths(2)->startOfMonth()->addDays(9),  2, 'approved',  'Liburan keluarga'],
            [2, now()->subMonth()->startOfMonth()->addDays(14),   1, 'approved',  'Sakit — surat dokter'],
            [6, now()->subDays(12),                               1, 'rejected',  'Urusan pribadi'],
            [1, now()->addDays(10),                               3, 'pending',   'Cuti akhir pekan panjang'],
        ];
        if ($i % 3 === 0) {
            $rows[] = [4, now()->addDays(30), 3, 'draft', 'Rencana cuti menikah'];
        }

        foreach ($rows as [$typeId, $start, $days, $status, $reason]) {
            $s = $start->copy();
            while ($s->isWeekend()) {
                $s->addDay();
            }
            $end = $s->copy();
            for ($left = $days - 1; $left > 0;) {
                $end->addDay();
                if (! $end->isWeekend()) {
                    $left--;
                }
            }
            \App\Models\HR\LeaveRequest::create([
                'employee_id' => $e->id, 'leave_type_id' => $typeId,
                'start_date' => $s->toDateString(), 'end_date' => $end->toDateString(),
                'total_days' => $days, 'reason' => $reason . ' (data contoh)', 'status' => $status,
            ]);

            // Cuti/sakit yang disetujui & sudah lewat → absensi tanggal itu ikut berstatus sama.
            if ($status === 'approved' && $end->lte(now())) {
                for ($d = $s->copy(); $d->lte($end); $d->addDay()) {
                    if ($d->isWeekend()) continue;
                    AttendanceRecord::updateOrCreate(
                        ['employee_id' => $e->id, 'date' => $d->toDateString()],
                        ['company_id' => $e->company_id, 'check_in' => null, 'check_out' => null,
                         'status' => $typeId === 2 ? 'sakit' : 'cuti', 'late_minutes' => 0, 'overtime_minutes' => 0,
                         'source' => 'manual', 'notes' => $reason]
                    );
                }
            }
        }

        \App\Models\HR\LeaveBalance::firstOrCreate(
            ['employee_id' => $e->id, 'leave_type_id' => 1, 'year' => now()->year],
            ['allocated' => 12, 'used' => 3, 'carried_days' => 0]
        );
    }

    /** Saldo medical + beberapa klaim reimbursement (hanya karyawan yang punya akun user). */
    private function seedReimbursement(Employee $e, int $i): void
    {
        if (! $e->user_id || \App\Models\Reimbursement\ReimbursementRequest::where('user_id', $e->user_id)->exists()) {
            return;
        }

        \App\Models\Reimbursement\ReimbursementBalance::firstOrCreate(
            ['user_id' => $e->user_id, 'period_year' => now()->year, 'balance_type' => 'medical'],
            ['initial_balance' => 5000000, 'used_balance' => 0]
        );

        $claims = [
            ['approved',  now()->subDays(40), 'Klinik Sehat Sentosa', 'Demam & flu',     ['amount_doctor' => 150000, 'amount_medicine' => 235000, 'amount_administration' => 25000]],
            ['submitted', now()->subDays(8),  'RS Mitra Keluarga',    'Gastritis',       ['amount_doctor' => 300000, 'amount_lab' => 275000, 'amount_medicine' => 180000]],
            ['draft',     now()->subDays(1),  'Optik Melawai',        'Kacamata minus',  ['amount_glasses' => 650000, 'amount_lens' => 450000]],
        ];
        if ($i % 2 === 0) {
            $claims[] = ['rejected', now()->subDays(25), 'Klinik Gigi Ceria', 'Pemutihan gigi (kosmetik)', ['amount_dental' => 900000]];
        }

        foreach ($claims as [$status, $date, $inst, $diag, $amounts]) {
            $r = \App\Models\Reimbursement\ReimbursementRequest::create([
                'user_id' => $e->user_id, 'request_number' => \App\Models\Reimbursement\ReimbursementRequest::generateNumber(),
                'request_date' => $date->toDateString(), 'medical_for' => 'employee', 'marital_status' => 'married',
                'status' => $status, 'notes' => 'Data contoh',
                'rejection_reason' => $status === 'rejected' ? 'Tindakan kosmetik tidak ditanggung' : null,
            ]);
            \App\Models\Reimbursement\ReimbursementItem::create([
                'reimbursement_request_id' => $r->id, 'patient_name' => $e->name,
                'treatment_date' => $date->toDateString(), 'institution' => $inst, 'diagnose' => $diag,
                'total_claim' => array_sum($amounts),
            ] + $amounts);
            $r->recalculateTotal();
        }
    }

    private function fillProfile(Employee $e, int $i): void
    {
        $n = $i + 1;
        $dummy = [
            'office_phone'                => '0812' . str_pad((string) (3000100 + $n), 7, '0', STR_PAD_LEFT),
            'sim_number'                  => '2026' . str_pad((string) (880100 + $n), 8, '0', STR_PAD_LEFT),
            'phone'                       => '0857' . str_pad((string) (7100200 + $n), 7, '0', STR_PAD_LEFT),
            'emergency_contact_name'      => 'Siti Aminah ' . $n,
            'emergency_contact_relation'  => 'Istri',
            'emergency_contact_phone'     => '0813' . str_pad((string) (4400100 + $n), 7, '0', STR_PAD_LEFT),
            'emergency_contact2_name'     => 'Budi Santoso ' . $n,
            'emergency_contact2_relation' => 'Orang Tua',
            'emergency_contact2_phone'    => '0821' . str_pad((string) (5500100 + $n), 7, '0', STR_PAD_LEFT),
            'guarantee1_type'             => 'Ijazah Asli',
            'guarantee2_type'             => 'BPKB Kendaraan',
        ];

        $fill = [];
        foreach ($dummy as $col => $val) {
            if (blank($e->{$col})) {
                $fill[$col] = $val;
            }
        }

        // File jaminan contoh (PDF 1 halaman minimal) agar tautan "Lihat file" tampil.
        foreach ([1, 2] as $slot) {
            if (blank($e->{"guarantee{$slot}_file"})) {
                $path = "employee-guarantees/contoh-jaminan-{$e->id}-{$slot}.pdf";
                Storage::disk('local')->put($path, $this->tinyPdf("Contoh dokumen jaminan {$slot} — {$e->name}"));
                $fill["guarantee{$slot}_file"] = $path;
            }
        }

        if ($fill) {
            $e->forceFill($fill)->save();
        }
    }

    private function seedHistories(Employee $e, int $i): void
    {
        if (EmployeeHistory::where('employee_id', $e->id)->exists()) {
            return;
        }

        $rows = [
            ['basic_salary', '2024-01-01', 'Rp 6.500.000',  'Rp 7.500.000',  'Penyesuaian tahunan'],
            ['basic_salary', '2025-01-01', 'Rp 7.500.000',  'Rp 8.250.000',  'Kenaikan gaji berkala'],
            ['basic_salary', '2026-01-01', 'Rp 8.250.000',  'Rp 9.000.000',  'Kenaikan gaji berkala'],
            ['title',        '2024-07-01', 'Staff',         'Senior Staff',  'Promosi'],
            ['title',        '2026-03-01', 'Senior Staff',  'Supervisor',    'Promosi'],
            ['grade',        '2024-07-01', 'Grade 3',       'Grade 4',       'Mengikuti promosi'],
            ['grade',        '2026-03-01', 'Grade 4',       'Grade 5',       'Mengikuti promosi'],
            ['cost_center',  '2025-04-01', 'CC-100 — Operasional', 'CC-200 — Logistik', 'Rotasi departemen'],
            ['employee_status', '2023-10-01', 'Kontrak',    'Tetap',         'Pengangkatan karyawan tetap'],
        ];

        // Driver: hanya karyawan genap yang punya riwayat mutasi driver
        if ($i % 2 === 0) {
            $rows[] = ['driver_mutation', '2025-02-10', 'Unit B 1234 XY — Jakarta', 'Unit B 5678 ZZ — Surabaya', 'Mutasi penempatan driver'];
            $rows[] = ['driver_mutation', '2026-06-01', 'Unit B 5678 ZZ — Surabaya', 'Unit L 9012 AB — Medan', 'Mutasi penempatan driver'];
        }

        foreach ($rows as [$cat, $date, $old, $new, $note]) {
            EmployeeHistory::create([
                'employee_id' => $e->id, 'category' => $cat, 'effective_date' => $date,
                'old_value' => $old, 'new_value' => $new, 'note' => $note,
            ]);
        }
    }

    /** Absensi 3 bulan terakhir s.d. hari ini; pola variatif tapi deterministik. */
    private function seedAttendance(Employee $e): void
    {
        $start = now()->subMonths(2)->startOfMonth();
        $end   = now()->startOfDay();

        $existing = AttendanceRecord::where('employee_id', $e->id)->whereBetween('date', [$start, $end])
            ->pluck('date')->map(fn ($d) => $d->toDateString())->flip();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($d->isWeekend() || $existing->has($d->toDateString())) {
                continue;
            }

            $k = ($e->id * 7 + $d->day * 3) % 20;
            [$status, $in, $out, $late, $ot, $note] = match (true) {
                $k === 0  => ['alpha', null, null, 0, 0, 'Tanpa keterangan'],
                $k === 1  => ['sakit', null, null, 0, 0, 'Surat dokter'],
                $k === 2  => ['cuti',  null, null, 0, 0, 'Cuti tahunan'],
                $k === 3  => ['izin',  null, null, 0, 0, 'Urusan keluarga'],
                $k >= 4 && $k <= 6 => ['telat', '08:' . str_pad((string) (15 + $k * 4), 2, '0', STR_PAD_LEFT), '17:05', 15 + $k * 4 - 0, 0, null],
                $k === 7  => ['hadir', '07:55', '19:00', 0, 120, 'Lembur'],
                default   => ['hadir', '07:5' . ($k % 10), '17:0' . ($k % 6), 0, 0, null],
            };

            AttendanceRecord::create([
                'employee_id' => $e->id, 'company_id' => $e->company_id, 'date' => $d->toDateString(),
                'check_in' => $in, 'check_out' => $out, 'status' => $status,
                'late_minutes' => $late, 'overtime_minutes' => $ot, 'source' => 'manual', 'notes' => $note,
            ]);
        }
    }

    private function seedOvertime($employees): void
    {
        $statuses = ['approved', 'pending', 'rejected', 'draft'];
        foreach ($employees as $n => $e) {
            if (OvertimeRequest::where('employee_id', $e->id)->exists()) {
                continue;
            }
            OvertimeRequest::create([
                'employee_id' => $e->id, 'company_id' => $e->company_id,
                'requested_by_user_id' => $e->user_id, 'date' => Carbon::now()->subDays(5 + $n)->toDateString(),
                'planned_hours' => 2 + ($n % 3), 'reason' => 'Penyelesaian laporan akhir bulan (data contoh)',
                'status' => $statuses[$n % 4],
            ]);
        }
    }

    private function tinyPdf(string $text): string
    {
        $text = str_replace(['(', ')', '\\'], ['\\(', '\\)', '\\\\'], $text);
        $stream = "BT /F1 14 Tf 50 750 Td ({$text}) Tj ET";
        $objs = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objs as $n => $o) {
            $offsets[] = strlen($pdf);
            $pdf .= ($n + 1) . " 0 obj\n{$o}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objs) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d 00000 n \n", $off);
        }
        $pdf .= "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
