<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'center_id',
        'reservation_id',
        'check_in_date',
        'check_out_date',
        'tariff_type',
        'guest_count',
        'total_amount',
        'year',
        'jalali_year',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'guest_count' => 'integer',
        'total_amount' => 'decimal:0',
        'year' => 'integer',
        'jalali_year' => 'integer',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function getDaysSinceUsage(): int
    {
        return now()->diffInDays($this->check_out_date);
    }

    public function isWithinThreeYears(): bool
    {
        return $this->getDaysSinceUsage() < config('welfare.three_year_rule_days', 1095);
    }
}
