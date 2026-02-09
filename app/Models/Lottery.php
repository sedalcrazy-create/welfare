<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lottery extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'title',
        'description',
        'registration_start_date',
        'registration_end_date',
        'draw_date',
        'status',
        'algorithm',
        'drawn_at',
        'drawn_by',
        'total_participants',
        'total_winners',
    ];

    protected $casts = [
        'registration_start_date' => 'datetime',
        'registration_end_date' => 'datetime',
        'draw_date' => 'datetime',
        'drawn_at' => 'datetime',
        'total_participants' => 'integer',
        'total_winners' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_DRAWN = 'drawn';
    public const STATUS_APPROVAL = 'approval';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const ALGORITHM_WEIGHTED_RANDOM = 'weighted_random';
    public const ALGORITHM_PRIORITY_BASED = 'priority_based';

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(LotteryEntry::class);
    }

    public function drawnByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'drawn_by');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(LotteryEntry::class)->where('status', LotteryEntry::STATUS_WON);
    }

    public function pendingApprovals(): HasMany
    {
        return $this->hasMany(LotteryEntry::class)->where('status', LotteryEntry::STATUS_WON);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function canRegister(): bool
    {
        if (!$this->isOpen()) {
            return false;
        }

        $now = now();
        return $now >= $this->registration_start_date && $now <= $this->registration_end_date;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_CLOSED]);
    }

    public function scopeReadyToDraw($query)
    {
        return $query->where('status', self::STATUS_CLOSED)
            ->where('draw_date', '<=', now());
    }
}
