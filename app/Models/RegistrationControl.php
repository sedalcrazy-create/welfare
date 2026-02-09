<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationControl extends Model
{
    protected $fillable = [
        'rule_type',
        'is_active',
        'start_date',
        'end_date',
        'center_id',
        'period_id',
        'allow_registration',
        'message',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_registration' => 'boolean',
    ];

    // Relations
    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGlobal($query)
    {
        return $query->where('rule_type', 'global');
    }

    public function scopeDateRange($query)
    {
        return $query->where('rule_type', 'date_range');
    }

    public function scopeForCenter($query, int $centerId)
    {
        return $query->where('rule_type', 'center')
            ->where('center_id', $centerId);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('rule_type', 'period')
            ->where('period_id', $periodId);
    }

    public function scopeBlocking($query)
    {
        return $query->where('allow_registration', false);
    }

    // Main check method
    public static function isRegistrationAllowed(?int $centerId = null, ?int $periodId = null): array
    {
        // Priority 1: Check global rules
        $global = self::active()->global()->blocking()->first();
        if ($global) {
            return [
                'allowed' => false,
                'message' => $global->message ?? 'ثبت نام در حال حاضر غیرفعال است',
                'rule_type' => 'global',
                'rule' => $global,
            ];
        }

        // Priority 2: Check date range rules
        $today = jdate()->format('Y-m-d');
        $dateRule = self::active()
            ->dateRange()
            ->blocking()
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        if ($dateRule) {
            return [
                'allowed' => false,
                'message' => $dateRule->message ?? 'ثبت نام در این بازه زمانی غیرفعال است',
                'rule_type' => 'date_range',
                'rule' => $dateRule,
            ];
        }

        // Priority 3: Check center rules
        if ($centerId) {
            $centerRule = self::active()
                ->forCenter($centerId)
                ->blocking()
                ->first();

            if ($centerRule) {
                return [
                    'allowed' => false,
                    'message' => $centerRule->message ?? 'ثبت نام برای این مرکز غیرفعال است',
                    'rule_type' => 'center',
                    'rule' => $centerRule,
                ];
            }
        }

        // Priority 4: Check period rules
        if ($periodId) {
            $periodRule = self::active()
                ->forPeriod($periodId)
                ->blocking()
                ->first();

            if ($periodRule) {
                return [
                    'allowed' => false,
                    'message' => $periodRule->message ?? 'ثبت نام برای این دوره غیرفعال است',
                    'rule_type' => 'period',
                    'rule' => $periodRule,
                ];
            }
        }

        return [
            'allowed' => true,
            'message' => null,
            'rule_type' => null,
            'rule' => null,
        ];
    }

    // Helper to describe rule
    public function getDescriptionAttribute(): string
    {
        return match ($this->rule_type) {
            'global' => 'کل سیستم',
            'date_range' => "بازه زمانی: {$this->start_date} تا {$this->end_date}",
            'center' => "مرکز: " . ($this->center->name ?? '-'),
            'period' => "دوره: " . ($this->period->name ?? '-'),
            default => 'نامشخص',
        };
    }
}
