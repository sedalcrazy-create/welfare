<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'personnel_count',
        'quota_ratio',
        'is_tehran',
        'is_active',
    ];

    protected $casts = [
        'personnel_count' => 'integer',
        'quota_ratio' => 'decimal:4',
        'is_tehran' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function lotteryEntries(): HasMany
    {
        return $this->hasMany(LotteryEntry::class);
    }

    public function calculateQuota(int $totalCapacity): int
    {
        return (int) round($totalCapacity * $this->quota_ratio);
    }
}
