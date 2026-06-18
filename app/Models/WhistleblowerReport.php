<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Model;

class WhistleblowerReport extends Model
{
    use HasHashid;

    protected $fillable = [
        'ticket_number',
        'category',
        'description',
        'is_anonymous',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'attachment_path',
        'attachment_original_name',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'reviewed_at'  => 'datetime',
    ];

    public static $categories = [
        'Pelanggaran Etika',
        'Pelecehan / Bullying',
        'Korupsi / Fraud',
        'Keselamatan Kerja',
        'Lainnya',
    ];

    public static $statuses = [
        'new'       => ['label' => 'Baru',      'badge' => 'danger'],
        'in_review' => ['label' => 'Diproses',  'badge' => 'warning'],
        'resolved'  => ['label' => 'Selesai',   'badge' => 'success'],
        'closed'    => ['label' => 'Ditutup',   'badge' => 'secondary'],
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::$statuses[$this->status]['label'] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::$statuses[$this->status]['badge'] ?? 'secondary';
    }

    public static function generateTicket(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)->max('id') ?? 0;
        return 'WB-' . $year . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}
