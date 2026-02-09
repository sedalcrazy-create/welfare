<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personnel extends Model
{
    use HasFactory;

    protected $table = 'personnel';

    protected $fillable = [
        'province_id',
        'employee_code',
        'national_code',
        'full_name',
        'first_name',
        'last_name',
        'father_name',
        'gender',
        'birth_date',
        'phone',
        'email',
        'bale_user_id',
        'employment_status',
        'service_location',
        'department',
        'service_years',
        'hire_date',
        'family_count',
        'is_isargar',
        'isargar_type',
        'isargar_percentage',
        'is_active',
    ];

    protected $casts = [
        'service_years' => 'integer',
        'family_count' => 'integer',
        'is_isargar' => 'boolean',
        'isargar_percentage' => 'integer',
        'is_active' => 'boolean',
    ];

    public const GENDER_MALE = 'male';
    public const GENDER_FEMALE = 'female';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_RETIRED = 'retired';

    public const ISARGAR_VETERAN = 'veteran';
    public const ISARGAR_FREED_POW = 'freed_pow';
    public const ISARGAR_MARTYR_CHILD = 'martyr_child';
    public const ISARGAR_MARTYR_SPOUSE = 'martyr_spouse';
    public const ISARGAR_MARTYR_PARENT = 'martyr_parent';

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lotteryEntries(): HasMany
    {
        return $this->hasMany(LotteryEntry::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function usageHistories(): HasMany
    {
        return $this->hasMany(UsageHistory::class);
    }

    public function getLastUsageForCenter(int $centerId): ?UsageHistory
    {
        return $this->usageHistories()
            ->where('center_id', $centerId)
            ->orderBy('check_out_date', 'desc')
            ->first();
    }

    public function getDaysSinceLastUsage(int $centerId): ?int
    {
        $lastUsage = $this->getLastUsageForCenter($centerId);

        if (!$lastUsage) {
            return null;
        }

        return now()->diffInDays($lastUsage->check_out_date);
    }

    public function isEligibleForBankRate(int $centerId): bool
    {
        $daysSinceLastUsage = $this->getDaysSinceLastUsage($centerId);

        if ($daysSinceLastUsage === null) {
            return true; // هرگز استفاده نکرده
        }

        return $daysSinceLastUsage >= config('welfare.three_year_rule_days', 1095);
    }

    public function getIsargarDiscount(): int
    {
        if (!$this->is_isargar) {
            return 0;
        }

        // جانبازان اعصاب و روان بالای ۲۵٪ و جانبازان ۷۰٪+
        if ($this->isargar_type === self::ISARGAR_VETERAN) {
            if ($this->isargar_percentage >= 70) {
                return 100; // رایگان
            }
        }

        return 50; // ۵۰٪ تخفیف
    }
}
