<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateDocument extends Model
{
    protected $fillable = ['candidate_id', 'doc_type', 'title', 'file_path', 'original_name', 'notes'];

    public static array $docTypes = [
        'ktp'    => 'KTP',
        'ijazah' => 'Ijazah',
        'cv'     => 'CV / Resume',
        'mcu'    => 'Hasil MCU',
        'skck'   => 'SKCK',
        'npwp'   => 'NPWP',
        'other'  => 'Lainnya',
    ];

    public function candidate(): BelongsTo { return $this->belongsTo(Candidate::class); }
}
