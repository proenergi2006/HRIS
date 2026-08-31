<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterRequest extends Model
{
    use HasHashid;

    protected $fillable = [
        'employee_id', 'requested_by_user_id', 'letter_template_id', 'purpose', 'notes',
        'status', 'employee_letter_id', 'handled_by_user_id', 'handled_at', 'rejection_note',
    ];

    protected $casts = ['handled_at' => 'datetime'];

    public static array $statusLabels = [
        'pending'   => 'Menunggu Diproses',
        'processed' => 'Selesai Diterbitkan',
        'rejected'  => 'Ditolak',
        'cancelled' => 'Dibatalkan',
    ];

    public static array $statusBadges = [
        'pending'   => 'warning',
        'processed' => 'success',
        'rejected'  => 'danger',
        'cancelled' => 'secondary',
    ];

    public static array $purposeLabels = [
        'bank'      => 'Persyaratan Bank',
        'visa'      => 'Visa / Perjalanan',
        'beasiswa'  => 'Beasiswa',
        'kpr'       => 'KPR / Kredit',
        'lainnya'   => 'Lainnya',
    ];

    public function employee(): BelongsTo      { return $this->belongsTo(Employee::class); }
    public function requestedBy(): BelongsTo   { return $this->belongsTo(User::class, 'requested_by_user_id'); }
    public function template(): BelongsTo      { return $this->belongsTo(LetterTemplate::class, 'letter_template_id'); }
    public function issuedLetter(): BelongsTo  { return $this->belongsTo(EmployeeLetter::class, 'employee_letter_id'); }
    public function handledBy(): BelongsTo     { return $this->belongsTo(User::class, 'handled_by_user_id'); }

    public function statusLabel(): string  { return self::$statusLabels[$this->status] ?? $this->status; }
    public function purposeLabel(): string { return self::$purposeLabels[$this->purpose] ?? ($this->purpose ?? '-'); }
}
