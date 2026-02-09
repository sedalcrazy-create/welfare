<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Center extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'city',
        'type',
        'description',
        'address',
        'phone',
        'bed_count',
        'unit_count',
        'stay_duration',
        'check_in_time',
        'check_out_time',
        'amenities',
        'is_active',
    ];

    protected $casts = [
        'bed_count' => 'integer',
        'unit_count' => 'integer',
        'stay_duration' => 'integer',
        'amenities' => 'array',
        'is_active' => 'boolean',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class);
    }

    public function activeUnits(): HasMany
    {
        return $this->hasMany(Unit::class)->where('status', 'active');
    }

    public function getActiveBedCountAttribute(): int
    {
        return $this->activeUnits()->sum('bed_count');
    }
}
