<?php

namespace App\Models\HR;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosterEntry extends Model
{
    protected $fillable = ['employee_id', 'company_id', 'work_date', 'shift_id', 'notes'];

    protected $casts = ['work_date' => 'date'];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function shift(): BelongsTo    { return $this->belongsTo(Shift::class); }
}
