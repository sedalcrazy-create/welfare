<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'personnel_id',
        'province_id',
        'phone',
        'bale_user_id',
        'is_active',
        'last_login_at',
        'quota_total',
        'quota_used',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'quota_total' => 'integer',
            'quota_used' => 'integer',
        ];
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'admin']);
    }

    public function isProvincialAdmin(): bool
    {
        return $this->hasRole('provincial_admin');
    }

    public function canManageProvince(int $provinceId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isProvincialAdmin()) {
            return $this->province_id === $provinceId;
        }

        return false;
    }

    public function introductionLetters()
    {
        return $this->hasMany(IntroductionLetter::class, 'issued_by_user_id');
    }

    public function assignedLetters()
    {
        return $this->hasMany(IntroductionLetter::class, 'assigned_user_id');
    }

    public function centerQuotas()
    {
        return $this->hasMany(UserCenterQuota::class);
    }

    public function getQuotaRemaining(): int
    {
        return max(0, $this->quota_total - $this->quota_used);
    }

    public function hasQuotaAvailable(int $count = 1): bool
    {
        return $this->getQuotaRemaining() >= $count;
    }

    public function incrementQuotaUsed(int $count = 1): bool
    {
        if (!$this->hasQuotaAvailable($count)) {
            return false;
        }

        $this->increment('quota_used', $count);
        return true;
    }

    public function decrementQuotaUsed(int $count = 1): bool
    {
        if ($this->quota_used < $count) {
            return false;
        }

        $this->decrement('quota_used', $count);
        return true;
    }

    // Per-Center Quota Methods
    public function getQuotaForCenter(int $centerId): ?UserCenterQuota
    {
        return $this->centerQuotas()->where('center_id', $centerId)->first();
    }

    public function hasQuotaForCenter(int $centerId, int $count = 1): bool
    {
        $quota = $this->getQuotaForCenter($centerId);
        return $quota && $quota->hasAvailable($count);
    }

    public function getTotalQuotaUsed(): int
    {
        return $this->centerQuotas()->sum('quota_used');
    }

    public function getTotalQuotaRemaining(): int
    {
        return $this->centerQuotas()->sum('quota_remaining');
    }
}
