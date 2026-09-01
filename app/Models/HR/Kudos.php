<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Recognition / apresiasi non-finansial antar karyawan — "Kudos Wall". */
class Kudos extends Model
{
    protected $fillable = ['from_employee_id', 'to_employee_id', 'company_id', 'category', 'message'];

    public static array $categoryLabels = [
        'teamwork'       => 'Kerja Sama Tim',
        'innovation'     => 'Inovasi',
        'leadership'     => 'Kepemimpinan',
        'customer_focus' => 'Fokus Pelanggan',
        'integrity'      => 'Integritas',
        'excellence'     => 'Kinerja Unggul',
    ];

    public static array $categoryIcons = [
        'teamwork'       => 'gd-user',
        'innovation'     => 'gd-light-bulb',
        'leadership'     => 'gd-target',
        'customer_focus' => 'gd-heart',
        'integrity'      => 'gd-shield',
        'excellence'     => 'gd-star',
    ];

    public function fromEmployee(): BelongsTo { return $this->belongsTo(Employee::class, 'from_employee_id'); }
    public function toEmployee(): BelongsTo   { return $this->belongsTo(Employee::class, 'to_employee_id'); }
    public function company(): BelongsTo      { return $this->belongsTo(Company::class); }
}
