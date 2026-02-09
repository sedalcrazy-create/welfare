<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LotteryEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_id',
        'personnel_id',
        'province_id',
        'family_count',
        'guests',
        'preferred_unit_types',
        'priority_score',
        'rank',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'family_count' => 'integer',
        'guests' => 'array',
        'preferred_unit_types' => 'array',
        'priority_score' => 'decimal:2',
        'rank' => 'integer',
        'approved_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';
    public const STATUS_WAITLIST = 'waitlist';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public function lottery(): BelongsTo
    {
        return $this->belongsTo(Lottery::class);
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reservation(): HasOne
    {
        return $this->hasOne(Reservation::class);
    }

    public function isWinner(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_WON && $this->approved_by === null;
    }

    public function approve(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject(int $userId, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function scopeByProvince($query, int $provinceId)
    {
        return $query->where('province_id', $provinceId);
    }

    public function scopeWinners($query)
    {
        return $query->whereIn('status', [self::STATUS_WON, self::STATUS_APPROVED]);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_WON)
            ->whereNull('approved_by');
    }
}
