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
}
