<?php

namespace App\Console\Commands;

use App\Mail\ContractExpiryReminderMail;
use App\Models\Employee;
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
        $recipients  = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('id', $adminRoles))
            ->whereNotNull('email')
            ->pluck('email')
            ->unique();

        if ($recipients->isEmpty()) {
            $this->warn('Tidak ada penerima email (admin/hr_manager) ditemukan.');
            return;
        }

        foreach ($recipients as $email) {
            Mail::to($email)->send(new ContractExpiryReminderMail($expiring, $expired));
        }

        $this->info("Email dikirim ke {$recipients->count()} penerima. Expiring: {$expiring->count()}, Expired: {$expired->count()}.");
    }
}
