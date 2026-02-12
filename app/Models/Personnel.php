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
        'family_members',
        'is_isargar',
        'isargar_type',
        'isargar_percentage',
        'is_active',
        'status',
        'registration_source',
        'preferred_center_id',
        'preferred_period_id',
        'notes',
        'tracking_code',
    ];

    protected $casts = [
        'service_years' => 'integer',
        'family_count' => 'integer',
        'family_members' => 'array',
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

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_BALE_BOT = 'bale_bot';
    public const SOURCE_WEB = 'web';

    // Family member relations
    public const RELATION_SPOUSE = 'همسر';
    public const RELATION_CHILD = 'فرزند';
    public const RELATION_FATHER = 'پدر';
    public const RELATION_MOTHER = 'مادر';
    public const RELATION_OTHER = 'سایر';

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

    public function introductionLetters(): HasMany
    {
        return $this->hasMany(IntroductionLetter::class);
    }

    public function preferredCenter(): BelongsTo
    {
        return $this->belongsTo(Center::class, 'preferred_center_id');
    }

    public function preferredPeriod(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'preferred_period_id');
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

    // Scopes for Phase 1
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeFromBaleBot($query)
    {
        return $query->where('registration_source', self::SOURCE_BALE_BOT);
    }

    /**
     * Generate unique tracking code
     */
    public static function generateTrackingCode(): string
    {
        do {
            $code = 'REQ-' . strtoupper(substr(uniqid(), -8));
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    // Family members helper methods
    public function getFamilyMembersCount(): int
    {
        return $this->family_members ? count($this->family_members) : 0;
    }

    public function getTotalPersonsCount(): int
    {
        // سرپرست + همراهان
        return 1 + $this->getFamilyMembersCount();
    }

    public function hasFamilyMembers(): bool
    {
        return !empty($this->family_members);
    }

    // Boot method for auto-calculating family_count
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($personnel) {
            // محاسبه خودکار family_count از روی family_members
            if (isset($personnel->family_members)) {
                $personnel->family_count = count($personnel->family_members) + 1; // +1 برای خود سرپرست
            }
        });
    }
}
