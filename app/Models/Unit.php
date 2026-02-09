<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'center_id',
        'number',
        'name',
        'type',
        'bed_count',
        'floor',
        'block',
        'amenities',
        'is_management',
        'status',
        'notes',
    ];

    protected $casts = [
        'bed_count' => 'integer',
        'floor' => 'integer',
        'amenities' => 'array',
        'is_management' => 'boolean',
    ];

    public const TYPE_ROOM = 'room';
    public const TYPE_SUITE = 'suite';
    public const TYPE_VILLA = 'villa';
    public const TYPE_APARTMENT = 'apartment';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_BLOCKED = 'blocked';

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByBedCount($query, int $minBeds, int $maxBeds)
    {
        return $query->whereBetween('bed_count', [$minBeds, $maxBeds]);
    }
}
