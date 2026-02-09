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
        'start_date',
        'end_date',
        'jalali_start_date',
        'jalali_end_date',
        'capacity',
        'reserved_count',
        'status',
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

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }
}
