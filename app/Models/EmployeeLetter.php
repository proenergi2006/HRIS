<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLetter extends Model
{
    use HasHashid;

    protected $fillable = [
        'employee_id', 'letter_template_id', 'letter_number',
        'category', 'title', 'body', 'issued_date', 'issued_by_user_id',
    ];

    protected $casts = ['issued_date' => 'date'];

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function template(): BelongsTo { return $this->belongsTo(LetterTemplate::class, 'letter_template_id'); }
    public function issuedBy(): BelongsTo  { return $this->belongsTo(User::class, 'issued_by_user_id'); }

    public function categoryLabel(): string
    {
        return LetterTemplate::$categoryLabels[$this->category] ?? $this->category;
    }
}
