<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgChangeLog extends Model
{
    protected $fillable = [
        'unit_type', 'unit_id', 'unit_name', 'action',
        'changes', 'effective_date', 'changed_by', 'note',
    ];

    protected $casts = [
        'changes'        => 'array',
        'effective_date' => 'date',
    ];

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getUnitTypeLabelAttribute(): string
    {
        return match ($this->unit_type) {
            'division'   => 'Divisi',
            'department' => 'Departemen',
            'section'    => 'Section',
            'position'   => 'Jabatan',
            default      => $this->unit_type,
        };
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created'     => 'Dibuat',
            'updated'     => 'Diubah',
            'moved'       => 'Dipindah',
            'deactivated' => 'Dinonaktifkan',
            'deleted'     => 'Dihapus',
            default       => $this->action,
        };
    }
}
