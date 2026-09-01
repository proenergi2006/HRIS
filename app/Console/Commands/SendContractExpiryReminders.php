<?php

namespace App\Console\Commands;

use App\Mail\ContractExpiryReminderMail;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class SendContractExpiryReminders extends Command
{
    protected $signature   = 'contract:remind';
    protected $description = 'Kirim email pengingat kontrak karyawan yang akan/sudah berakhir ke admin HRD';

    public function handle(): void
    {
        $expiring = Employee::where('employment_status', 'contract')
            ->where('is_active', true)
            ->whereNotNull('contract_end_date')
            ->whereBetween('contract_end_date', [
                now()->toDateString(),
                now()->addDays(60)->toDateString(),
            ])
            ->orderBy('contract_end_date')
            ->get();

        $expired = Employee::where('employment_status', 'contract')
            ->where('is_active', true)
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '<', now()->toDateString())
            ->orderBy('contract_end_date')
            ->get();

        if ($expiring->isEmpty() && $expired->isEmpty()) {
            $this->info('Tidak ada kontrak yang perlu diingatkan.');
            return;
        }

        $adminRoles  = Role::whereIn('name', ['admin', 'hr_manager'])->pluck('id');
        $recipientUsers = User::whereHas('roles', fn($q) => $q->whereIn('id', $adminRoles))
            ->whereNotNull('email')
            ->get();

        if ($recipientUsers->isEmpty()) {
            $this->warn('Tidak ada penerima email (admin/hr_manager) ditemukan.');
            return;
        }

        foreach ($recipientUsers as $user) {
            Mail::to($user->email)->send(new ContractExpiryReminderMail($expiring, $expired));
            $user->notify(new GenericNotification(
                'Pengingat Kontrak Karyawan',
                $expiring->count() . ' kontrak akan berakhir, ' . $expired->count() . ' sudah berakhir.',
                route('laporan.headcount'),
                'gd-calendar'
            ));
        }

        $this->info("Email + notifikasi dikirim ke {$recipientUsers->count()} penerima. Expiring: {$expiring->count()}, Expired: {$expired->count()}.");
    }
}
