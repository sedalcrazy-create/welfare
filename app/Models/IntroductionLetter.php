<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntroductionLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_code',
        'personnel_id',
        'center_id',
        'period_id',
        'issued_by_user_id',
        'assigned_user_id',
        'family_count',
        'notes',
        'valid_from',
        'valid_until',
        'issued_at',
        'used_at',
        'status',
        'cancellation_reason',
        'cancelled_by_user_id',
        'cancelled_at',
    ];

    protected $casts = [
        'family_count' => 'integer',
        'issued_at' => 'datetime',
        'used_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
        'family_count' => 1,
    ];

    // Relations
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(Center::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForCenter($query, $centerId)
    {
        return $query->where('center_id', $centerId);
    }

    // Methods
    public function markAsUsed(): bool
    {
        return $this->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    public function cancel(string $reason, int $userId): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by_user_id' => $userId,
            'cancelled_at' => now(),
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Generate unique letter code
     * Format: {CENTER_CODE}-{YYمم}-{SEQUENCE}
     * Example: MAS-0501-0001
     */
    public static function generateLetterCode(Center $center, ?Period $period = null): string
    {
        $centerCodes = [
            'mashhad' => 'MAS',
            'babolsar' => 'BAB',
            'chadegan' => 'CHA',
        ];

        $prefix = $centerCodes[$center->slug] ?? strtoupper(substr($center->slug, 0, 3));

        // استخراج سال و ماه از تاریخ شروع دوره یا تاریخ امروز
        if ($period) {
            $jalaliDate = jdate($period->start_date);
        } else {
            $jalaliDate = jdate(now());
        }
        $yearMonth = $jalaliDate->format('ym'); // 0501

        // شماره ترتیبی برای این مرکز
        $query = static::where('center_id', $center->id)
            ->where('letter_code', 'like', "{$prefix}-{$yearMonth}-%");

        if ($period) {
            $query->where('period_id', $period->id);
        }

        $lastLetter = $query->latest('id')->first();

        $sequence = $lastLetter ? ((int) substr($lastLetter->letter_code, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $yearMonth, $sequence);
    }
}
