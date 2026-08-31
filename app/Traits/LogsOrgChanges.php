<?php

namespace App\Traits;

use App\Models\OrgChangeLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Dipakai controller unit organisasi (Division/Department/Section/Position)
 * untuk mencatat jejak perubahan struktur ke tabel org_change_logs.
 * Dipanggil eksplisit di store/update/destroy — bukan lewat observer —
 * supaya niat perubahan (action, tanggal efektif, catatan) tetap jelas.
 */
trait LogsOrgChanges
{
    protected function logOrgChange(
        string $unitType,
        Model $unit,
        string $action,
        array $changes = [],
        Carbon|string|null $effectiveDate = null,
        ?string $note = null,
    ): void {
        OrgChangeLog::create([
            'unit_type'      => $unitType,
            'unit_id'        => $unit->getKey(),
            'unit_name'      => $unit->name ?? null,
            'action'         => $action,
            'changes'        => $changes ?: null,
            'effective_date' => $effectiveDate ? Carbon::parse($effectiveDate) : now()->toDateString(),
            'changed_by'     => Auth::id(),
            'note'           => $note,
        ]);
    }

    /**
     * Bandingkan atribut model sebelum & sesudah update.
     * Kembalikan map {field: {from, to}} hanya untuk yang berubah.
     *
     * @param  array<int, string>  $fields
     * @return array<string, array{from: mixed, to: mixed}>
     */
    protected function diffOrgAttributes(Model $unit, array $original, array $fields): array
    {
        $diff = [];

        foreach ($fields as $field) {
            $before = $original[$field] ?? null;
            $after  = $unit->getAttribute($field);

            if ((string) $before !== (string) $after) {
                $diff[$field] = ['from' => $before, 'to' => $after];
            }
        }

        return $diff;
    }
}
