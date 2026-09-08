<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * PT. Tridaya Selaras (TDS) punya Divisi BOD/Commercial/Logistik yang masih
     * kosong (0 Jabatan) — akibatnya bagan organisasi tidak bisa menampilkan
     * "Commercial/Logistik lapor ke BOD" karena tidak ada Jabatan sama sekali
     * utk direkrut/ditempatkan. Isi jabatan dasar dari nol per Divisi, plus
     * "Direktur Utama" di BOD sbg induk pelaporan Commercial & Logistik
     * (reports_to_position_id — dipakai halaman Jabatan; garis di bagan
     * organisasi sendiri mengikuti Employee.manager_id saat direkrut).
     */
    public function up(): void
    {
        $company = DB::table('companies')->where('name', 'PT. Tridaya Selaras')->first();
        if (! $company) {
            return; // company TDS tidak ditemukan — lewati, aman di-skip.
        }

        $deptBod = DB::table('departments')->where('company_id', $company->id)->where('name', 'BOD')->value('id');
        $deptCommercial = DB::table('departments')->where('company_id', $company->id)->where('name', 'COMMERCIAL TDS')->value('id');
        $deptLogistik = DB::table('departments')->where('company_id', $company->id)->where('name', 'LOGISTIK TDS')->value('id');

        $levelDireksi = DB::table('levels')->where('name', 'Direksi')->value('id');
        $levelManager = DB::table('levels')->where('name', 'Manager')->value('id');
        $levelSpv = DB::table('levels')->where('name', 'SPV')->value('id');
        $levelSeniorStaff = DB::table('levels')->where('name', 'Senior Staff')->value('id');
        $levelStaff = DB::table('levels')->where('name', 'Staff')->value('id');

        if (! $deptBod || ! $deptCommercial || ! $deptLogistik || ! $levelDireksi || ! $levelManager || ! $levelSpv || ! $levelSeniorStaff || ! $levelStaff) {
            return; // struktur Divisi/Departemen/Level belum sesuai asumsi — lewati.
        }

        $now = now();

        $existing = DB::table('positions')->where('company_id', $company->id)
            ->whereIn('code', ['BOD-DIR', 'COMM-TDS-MGR', 'COMM-TDS-SPV', 'COMM-TDS-SST', 'COMM-TDS-STF', 'LOG-TDS-MGR', 'LOG-TDS-SPV', 'LOG-TDS-SST', 'LOG-TDS-STF'])
            ->pluck('id', 'code');

        $insert = function (string $code, string $name, int $deptId, int $levelId, int $tunjangan) use ($company, $now, $existing) {
            if ($existing->has($code)) {
                return $existing[$code];
            }

            return DB::table('positions')->insertGetId([
                'company_id'        => $company->id,
                'department_id'     => $deptId,
                'level_id'          => $levelId,
                'code'              => $code,
                'name'              => $name,
                'tunjangan_jabatan' => $tunjangan,
                'is_active'         => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        };

        $direkturId = $insert('BOD-DIR', 'Direktur Utama', $deptBod, $levelDireksi, 20_000_000);

        $mgrCommId = $insert('COMM-TDS-MGR', 'Manager Commercial TDS', $deptCommercial, $levelManager, 5_000_000);
        $insert('COMM-TDS-SPV', 'SPV Commercial TDS', $deptCommercial, $levelSpv, 2_500_000);
        $insert('COMM-TDS-SST', 'Senior Staff Commercial TDS', $deptCommercial, $levelSeniorStaff, 1_200_000);
        $insert('COMM-TDS-STF', 'Staff Commercial TDS', $deptCommercial, $levelStaff, 500_000);

        $mgrLogId = $insert('LOG-TDS-MGR', 'Manager Logistik TDS', $deptLogistik, $levelManager, 5_000_000);
        $insert('LOG-TDS-SPV', 'SPV Logistik TDS', $deptLogistik, $levelSpv, 2_500_000);
        $insert('LOG-TDS-SST', 'Senior Staff Logistik TDS', $deptLogistik, $levelSeniorStaff, 1_200_000);
        $insert('LOG-TDS-STF', 'Staff Logistik TDS', $deptLogistik, $levelStaff, 500_000);

        DB::table('positions')->whereIn('id', [$mgrCommId, $mgrLogId])->update(['reports_to_position_id' => $direkturId]);
    }

    public function down(): void
    {
        $company = DB::table('companies')->where('name', 'PT. Tridaya Selaras')->first();
        if (! $company) {
            return;
        }

        DB::table('positions')->where('company_id', $company->id)
            ->whereIn('code', ['BOD-DIR', 'COMM-TDS-MGR', 'COMM-TDS-SPV', 'COMM-TDS-SST', 'COMM-TDS-STF', 'LOG-TDS-MGR', 'LOG-TDS-SPV', 'LOG-TDS-SST', 'LOG-TDS-STF'])
            ->delete();
    }
};
