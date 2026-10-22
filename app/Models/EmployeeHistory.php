<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeHistory extends Model
{
    protected $fillable = [
        'employee_id', 'category', 'effective_date', 'old_value', 'new_value', 'note', 'changed_by_user_id',
    ];

    protected $casts = ['effective_date' => 'date'];

    /** kategori => [label, label kolom "data terbaru"] */
    public const CATEGORIES = [
        'basic_salary'    => ['Basic Salary',     'New Salary'],
        'title'           => ['Title',            'New Title'],
        'grade'           => ['Grade',            'New Grade'],
        'cost_center'     => ['Cost Center',      'New Cost Center'],
        'employee_status' => ['Employee Status',  'New Status'],
        'driver_mutation' => ['Mutasi Driver',    'Penempatan Baru'],
    ];

    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by_user_id'); }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category][0] ?? $this->category;
    }
}
