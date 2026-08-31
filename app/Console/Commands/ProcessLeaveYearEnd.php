<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeavePolicy;
use App\Models\HR\LeaveType;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Alokasi saldo cuti tahun baru + carry-forward sisa cuti tahun lalu (dibatasi
 * LeavePolicy::carry_forward_max_days), dengan tanggal hangus carry_forward_expire_month.
 */
class ProcessLeaveYearEnd extends Command
{
    protected $signature = 'leave:year-end {year? : Tahun target, default tahun berjalan}';
    protected $description = 'Alokasi saldo cuti tahun baru + carry-forward sisa cuti tahun lalu sesuai kebijakan cuti';

    public function handle(): void
    {
        $year     = (int) ($this->argument('year') ?: now()->year);
        $prevYear = $year - 1;

        $employees  = Employee::where('is_active', true)->whereNotNull('start_date')->get();
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $count      = 0;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                $policy = LeavePolicy::resolveFor($employee, $leaveType, $year);
                if (! $policy) {
                    continue; // tidak ada kebijakan carry-forward — biarkan alokasi flat via forEmployee()
                }

                $prevBalance = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)->where('year', $prevYear)->first();
                $prevRemaining = $prevBalance ? $prevBalance->getRemaining() : 0;

                $carried = min((float) $policy->carry_forward_max_days, $prevRemaining);

                LeaveBalance::updateOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => $year],
                    [
                        'allocated'           => (float) $policy->quota_days + $carried,
                        'carried_days'        => $carried,
                        'carried_expires_on'  => Carbon::create($year, $policy->carry_forward_expire_month, 1)->endOfMonth(),
                    ]
                );
                $count++;
            }
        }

        $this->info("Alokasi saldo cuti {$year} selesai untuk {$count} baris (karyawan x jenis cuti ber-kebijakan).");
    }
}
