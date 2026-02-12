<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'center_id',
        'season_id',
        'code',
        'title',
        'description',
        'start_date',
        'end_date',
        'jalali_start_date',
        'jalali_end_date',
        'capacity',
        'reserved_count',
        'status',
        'season_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'capacity' => 'integer',
        'reserved_count' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_COMPLETED = 'completed';

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function lottery(): HasOne
    {
        return $this->hasOne(Lottery::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function personnelRequests(): HasMany
    {
        return $this->hasMany(Personnel::class, 'preferred_period_id');
    }

    public function introductionLetters(): HasMany
    {
        return $this->hasMany(IntroductionLetter::class, 'period_id');
    }

    public function getAvailableCapacity(): int
    {
        return max(0, $this->capacity - $this->reserved_count);
    }

    public function hasAvailableCapacity(): bool
    {
        return $this->getAvailableCapacity() > 0;
    }

    public function getStayDuration(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get registered count for Phase 1 (personnel requests + introduction letters)
     */
    public function getRegisteredCount(): int
    {
        return $this->personnelRequests()
            ->whereIn('status', [Personnel::STATUS_PENDING, Personnel::STATUS_APPROVED])
            ->count();
    }

    /**
     * Get remaining capacity for Phase 1
     */
    public function getRemainingCapacity(): int
    {
        return max(0, $this->capacity - $this->getRegisteredCount());
    }

    /**
     * Check if period is available for registration
     */
    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_OPEN
            && $this->getRemainingCapacity() > 0
            && $this->end_date >= now();
    }

    /**
     * Get start date in Jalali format
     */
    public function getStartDateJalaliAttribute(): string
    {
        return \Morilog\Jalali\Jalalian::fromCarbon($this->start_date)->format('Y/m/d');
    }

    /**
     * Get end date in Jalali format
     */
    public function getEndDateJalaliAttribute(): string
    {
        return \Morilog\Jalali\Jalalian::fromCarbon($this->end_date)->format('Y/m/d');
    }

    /**
     * Get duration in days
     */
    public function getDurationDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_OPEN)
            ->where('end_date', '>=', now());
    }
}
