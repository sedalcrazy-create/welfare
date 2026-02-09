<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'center_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'jalali_start_date',
        'jalali_end_date',
        'discount_rate',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_rate' => 'integer',
        'is_active' => 'boolean',
    ];

    public const TYPE_GOLDEN_PEAK = 'golden_peak';
    public const TYPE_PEAK = 'peak';
    public const TYPE_MID_SEASON = 'mid_season';
    public const TYPE_OFF_PEAK = 'off_peak';
    public const TYPE_SUPER_OFF_PEAK = 'super_off_peak';

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }

    public function isPeak(): bool
    {
        return in_array($this->type, [
            self::TYPE_GOLDEN_PEAK,
            self::TYPE_PEAK,
        ]);
    }

    public function isOffPeak(): bool
    {
        return in_array($this->type, [
            self::TYPE_OFF_PEAK,
            self::TYPE_SUPER_OFF_PEAK,
        ]);
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_GOLDEN_PEAK => 'پیک طلایی',
            self::TYPE_PEAK => 'پیک',
            self::TYPE_MID_SEASON => 'میان‌فصل',
            self::TYPE_OFF_PEAK => 'غیرپیک',
            self::TYPE_SUPER_OFF_PEAK => 'فوق‌العاده غیرپیک',
            default => $this->type,
        };
    }
}
