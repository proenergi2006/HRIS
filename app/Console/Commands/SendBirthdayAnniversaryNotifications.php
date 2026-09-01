<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

/**
 * Notifikasi in-app ulang tahun & hari jadi kerja (work anniversary) — ke karyawan
 * yang bersangkutan (ucapan) + ringkasan ke admin/hr_manager. Data sudah dipakai di
 * dashboard SDM (`HrReportBuilder::hrCalendar()`), command ini cuma tambahan
 * notifikasi bel supaya tidak perlu buka dashboard tiap hari.
 */
class SendBirthdayAnniversaryNotifications extends Command
{
    protected $signature   = 'hr:remind-birthdays';
    protected $description = 'Kirim notifikasi in-app ulang tahun & hari jadi kerja karyawan hari ini';

    public function handle(): void
    {
        $today = now();

        $birthdays = Employee::where('is_active', true)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->with('user')->get();

        $anniversaries = Employee::where('is_active', true)
            ->whereNotNull('start_date')
            ->whereMonth('start_date', $today->month)
            ->whereDay('start_date', $today->day)
            ->whereYear('start_date', '<', $today->year)
            ->with('user')->get();

        if ($birthdays->isEmpty() && $anniversaries->isEmpty()) {
            $this->info('Tidak ada ulang tahun/hari jadi kerja hari ini.');
            return;
        }

        foreach ($birthdays as $e) {
            $e->user?->notify(new GenericNotification(
                'Selamat Ulang Tahun! 🎉',
                'Selamat ulang tahun, ' . $e->name . '!',
                route('dashboard'),
                'gd-heart'
            ));
        }

        foreach ($anniversaries as $e) {
            $years = $e->start_date->diffInYears($today);
            $e->user?->notify(new GenericNotification(
                'Selamat Hari Jadi Kerja! 🎊',
                'Selamat ' . $years . ' tahun bergabung, ' . $e->name . '!',
                route('dashboard'),
                'gd-star'
            ));
        }

        if ($birthdays->isNotEmpty() || $anniversaries->isNotEmpty()) {
            $adminRoles = Role::whereIn('name', ['admin', 'hr_manager'])->pluck('id');
            $admins = User::whereHas('roles', fn ($q) => $q->whereIn('id', $adminRoles))->get();

            foreach ($admins as $admin) {
                $admin->notify(new GenericNotification(
                    'Ulang Tahun & Hari Jadi Kerja Hari Ini',
                    $birthdays->count() . ' ulang tahun, ' . $anniversaries->count() . ' hari jadi kerja.',
                    route('dashboard'),
                    'gd-calendar'
                ));
            }
        }

        $this->info("Notifikasi terkirim. Ulang tahun: {$birthdays->count()}, Hari jadi kerja: {$anniversaries->count()}.");
    }
}
