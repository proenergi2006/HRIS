<?php

namespace App\Models\Reimbursement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReimbursementItem extends Model
{
    public const AMOUNT_FIELDS = [
        'amount_administration' => 'Administrasi',
        'amount_doctor'         => 'Dokter',
        'amount_medicine'       => 'Obat',
        'amount_lab'            => 'Lab/Penunjang',
        'amount_consultation'   => 'Konsultasi',
        'amount_dental'         => 'Gigi',
        'amount_glasses'        => 'Kacamata',
        'amount_lens'           => 'Lensa',
        'amount_pregnancy'      => 'Kehamilan',
    ];

    protected $fillable = [
        'reimbursement_request_id', 'patient_name', 'treatment_date',
        'institution', 'diagnose',
        'amount_administration', 'amount_doctor', 'amount_medicine',
        'amount_lab', 'amount_consultation', 'amount_dental',
        'amount_glasses', 'amount_lens', 'amount_pregnancy',
        'total_claim',
    ];

    protected $casts = ['treatment_date' => 'date'];

    public function calculateTotal(): int
    {
        return (int) array_sum(array_map(
            fn($f) => (int) ($this->$f ?? 0),
            array_keys(self::AMOUNT_FIELDS)
        ));
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ReimbursementRequest::class, 'reimbursement_request_id');
    }
}
