<?php

namespace App\Models;

use App\Traits\HasHashid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    use HasHashid;

    protected $fillable = [
        'company_id', 'title', 'body', 'category', 'is_pinned', 'published_at', 'expires_at',
        'attachment_path', 'attachment_name', 'created_by_user_id', 'is_active',
    ];

    protected $casts = [
        'is_pinned'     => 'boolean',
        'is_active'     => 'boolean',
        'published_at'  => 'datetime',
        'expires_at'    => 'date',
    ];

    public static array $categoryLabels = [
        'info'      => 'Info',
        'kebijakan' => 'Kebijakan',
        'acara'     => 'Acara',
        'darurat'   => 'Darurat',
    ];

    public static array $categoryBadges = [
        'info'      => 'info',
        'kebijakan' => 'primary',
        'acara'     => 'success',
        'darurat'   => 'danger',
    ];

    public function company(): BelongsTo  { return $this->belongsTo(Company::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function reads(): HasMany      { return $this->hasMany(AnnouncementRead::class); }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()->toDateString()));
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $companyId = $user->employee?->company_id;

        return $query->where(fn ($q) => $q->whereNull('company_id')->when($companyId, fn ($qq) => $qq->orWhere('company_id', $companyId)));
    }

    public function isReadBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    public function categoryLabel(): string { return self::$categoryLabels[$this->category] ?? $this->category; }
    public function categoryBadge(): string { return self::$categoryBadges[$this->category] ?? 'secondary'; }
}
