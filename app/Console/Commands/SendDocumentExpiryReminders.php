<?php

namespace App\Console\Commands;

use App\Mail\DocumentExpiryReminderMail;
use App\Models\EmployeeDocument;
use App\Models\User;
use App\Notifications\GenericNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

/**
 * Pengingat masa berlaku dokumen karyawan (KTP/SIM/paspor/sertifikat/KITAS, dst) —
 * mirror SendContractExpiryReminders tapi sumbernya employee_documents.expires_at.
 */
class SendDocumentExpiryReminders extends Command
{
    protected $signature   = 'documents:remind';
    protected $description = 'Kirim email pengingat dokumen karyawan yang akan/sudah kadaluarsa ke admin HRD';

    public function handle(): void
    {
        $expiring = EmployeeDocument::whereNotNull('expires_at')
            ->whereHas('employee', fn ($q) => $q->where('is_active', true))
            ->whereBetween('expires_at', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->with('employee')
            ->orderBy('expires_at')
            ->get();

        $expired = EmployeeDocument::whereNotNull('expires_at')
            ->whereHas('employee', fn ($q) => $q->where('is_active', true))
            ->where('expires_at', '<', now()->toDateString())
            ->where('expires_at', '>=', now()->subDays(90)->toDateString())
            ->with('employee')
            ->orderBy('expires_at')
            ->get();

        if ($expiring->isEmpty() && $expired->isEmpty()) {
            $this->info('Tidak ada dokumen yang perlu diingatkan.');
            return;
        }

        $adminRoles = Role::whereIn('name', ['admin', 'hr_manager'])->pluck('id');
        $recipientUsers = User::whereHas('roles', fn ($q) => $q->whereIn('id', $adminRoles))
            ->whereNotNull('email')->get();

        if ($recipientUsers->isEmpty()) {
            $this->warn('Tidak ada penerima email (admin/hr_manager) ditemukan.');
            return;
        }

        foreach ($recipientUsers as $user) {
            Mail::to($user->email)->send(new DocumentExpiryReminderMail($expiring, $expired));
            $user->notify(new GenericNotification(
                'Pengingat Dokumen Karyawan',
                $expiring->count() . ' dokumen akan kadaluarsa, ' . $expired->count() . ' sudah kadaluarsa.',
                route('dashboard'),
                'gd-file'
            ));
        }

        $this->info("Email + notifikasi dikirim ke {$recipientUsers->count()} penerima. Akan kadaluarsa: {$expiring->count()}, Sudah kadaluarsa: {$expired->count()}.");
    }
}
