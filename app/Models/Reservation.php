<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lottery_entry_id',
        'personnel_id',
        'unit_id',
        'period_id',
        'reservation_code',
        'guest_count',
        'guests',
        'tariff_type',
        'accommodation_amount',
        'meal_amount',
        'discount_amount',
        'total_amount',
        'payment_status',
        'status',
        'check_in_at',
        'check_out_at',
        'checked_in_by',
        'checked_out_by',
        'cancellation_reason',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'guest_count' => 'integer',
        'guests' => 'array',
        'accommodation_amount' => 'decimal:0',
        'meal_amount' => 'decimal:0',
        'discount_amount' => 'decimal:0',
        'total_amount' => 'decimal:0',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public const TARIFF_BANK_RATE = 'bank_rate';
    public const TARIFF_FREE_BANK_RATE = 'free_bank_rate';
    public const TARIFF_FREE_NON_BANK_RATE = 'free_non_bank_rate';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_CHECKED_OUT = 'checked_out';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reservation) {
            if (empty($reservation->reservation_code)) {
                $reservation->reservation_code = self::generateReservationCode();
            }
        });
    }

    public static function generateReservationCode(): string
    {
        $prefix = 'WLF';
        $timestamp = now()->format('ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 4));

        return "{$prefix}-{$timestamp}-{$random}";
    }

    public function lotteryEntry(): BelongsTo
    {
        return $this->belongsTo(LotteryEntry::class);
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function checkedInByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function checkIn(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_CHECKED_IN,
            'check_in_at' => now(),
            'checked_in_by' => $userId,
        ]);
    }

    public function checkOut(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_CHECKED_OUT,
            'check_out_at' => now(),
            'checked_out_by' => $userId,
        ]);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    public function getTariffLabel(): string
    {
        return match($this->tariff_type) {
            self::TARIFF_BANK_RATE => 'نرخ بانکی',
            self::TARIFF_FREE_BANK_RATE => 'آزاد بانکی',
            self::TARIFF_FREE_NON_BANK_RATE => 'آزاد غیربانکی',
            default => $this->tariff_type,
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'در انتظار',
            self::STATUS_CONFIRMED => 'تأیید شده',
            self::STATUS_CHECKED_IN => 'ورود',
            self::STATUS_CHECKED_OUT => 'خروج',
            self::STATUS_CANCELLED => 'لغو شده',
            self::STATUS_NO_SHOW => 'عدم حضور',
            default => $this->status,
        };
    }
}
