<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerPathStep extends Model
{
    protected $fillable = ['career_path_id', 'position_id', 'step_order', 'notes'];

    public function careerPath(): BelongsTo { return $this->belongsTo(CareerPath::class); }
    public function position(): BelongsTo   { return $this->belongsTo(Position::class); }
}
