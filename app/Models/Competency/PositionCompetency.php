<?php

namespace App\Models\Competency;

use App\Models\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Kompetensi yang diwajibkan sebuah jabatan + level minimalnya. */
class PositionCompetency extends Model
{
    protected $fillable = ['position_id', 'competency_id', 'required_level', 'notes'];

    protected $casts = ['required_level' => 'integer'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }
}
