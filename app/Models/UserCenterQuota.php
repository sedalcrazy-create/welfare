<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCenterQuota extends Model
{
    protected $fillable = [
        'user_id',
        'center_id',
        'quota_total',
        'quota_used',
    ];

    protected $casts = [
        'quota_total' => 'integer',
        'quota_used' => 'integer',
    ];

    protected $appends = ['quota_remaining'];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    // Accessors
    public function getQuotaRemainingAttribute(): int
    {
        return max(0, $this->quota_total - $this->quota_used);
    }

    // Methods
    public function hasAvailable(int $count = 1): bool
    {
        return $this->quota_remaining >= $count;
    }

    public function incrementUsed(int $count = 1): bool
    {
        $this->increment('quota_used', $count);
        $this->refresh();
        return true;
    }

    public function decrementUsed(int $count = 1): bool
    {
        if ($this->quota_used >= $count) {
            $this->decrement('quota_used', $count);
            $this->refresh();
            return true;
        }
        return false;
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCenter($query, int $centerId)
    {
        return $query->where('center_id', $centerId);
    }

    public function scopeHasAvailable($query)
    {
        return $query->whereRaw('quota_total > quota_used');
    }

    // Static helpers
    public static function getOrCreate(int $userId, int $centerId): self
    {
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'center_id' => $centerId,
            ],
            [
                'quota_total' => 0,
                'quota_used' => 0,
            ]
        );
    }
}
