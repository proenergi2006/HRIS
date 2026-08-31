<?php

namespace App\Console\Commands;

use App\Models\HR\LeaveBalance;
use Illuminate\Console\Command;

/**
 * Hanguskan sisa carry-forward cuti yang sudah lewat carried_expires_on dan belum
 * terpakai. Carry dianggap terpakai lebih dulu (FIFO) dibanding kuota tahun berjalan.
 */
class ExpireLeaveCarryForward extends Command
{
    protected $signature = 'leave:expire-carry';
    protected $description = 'Hanguskan sisa carry-forward cuti yang sudah melewati tanggal kadaluarsa';

    public function handle(): void
    {
        $balances = LeaveBalance::whereNotNull('carried_expires_on')
            ->where('carried_expires_on', '<=', now()->toDateString())
            ->where('carried_days', '>', 0)
            ->get();

        $count = 0;
        foreach ($balances as $balance) {
            $carried = (float) $balance->carried_days;
            $used    = (float) $balance->used;
            $quota   = (float) $balance->allocated - $carried;

            // Carry dipakai lebih dulu: sisa carry yang belum terpakai = carry - max(0, used - quota).
            $unusedCarry = max(0, $carried - max(0, $used - $quota));

            if ($unusedCarry <= 0) {
                $balance->update(['carried_days' => 0]);
                continue;
            }

            $balance->update([
                'allocated'    => (float) $balance->allocated - $unusedCarry,
                'carried_days' => 0,
            ]);
            $count++;
        }

        $this->info("Carry-forward cuti dihanguskan untuk {$count} baris saldo.");
    }
}
