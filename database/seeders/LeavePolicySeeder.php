<?php

namespace Database\Seeders;

use App\Models\HR\LeavePolicy;
use App\Models\HR\LeaveType;
use Illuminate\Database\Seeder;

/**
 * Kebijakan cuti default (Fase 2 HRD) — starter set. Berlaku semua perusahaan & level:
 * Cuti Tahunan 12 hari/tahun, boleh dibawa maks 6 hari ke tahun berikutnya, hangus akhir Maret.
 */
class LeavePolicySeeder extends Seeder
{
    public function run(): void
    {
        $cutiTahunan = LeaveType::where('name', 'Cuti Tahunan')->first();
        if (! $cutiTahunan) {
            return;
        }

        LeavePolicy::firstOrCreate(
            ['company_id' => null, 'leave_type_id' => $cutiTahunan->id, 'level_id' => null, 'min_years_service' => 0],
            [
                'quota_days'                  => 12,
                'carry_forward_max_days'      => 6,
                'carry_forward_expire_month'  => 3,
                'is_active'                    => true,
            ]
        );
    }
}
