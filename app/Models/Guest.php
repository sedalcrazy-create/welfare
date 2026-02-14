<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'national_code',
        'full_name',
        'relation',
        'birth_date',
        'gender',
        'phone',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    /**
     * پرسنل‌هایی که این مهمان با آنها مرتبط است
     */
    public function personnel(): BelongsToMany
    {
        return $this->belongsToMany(Personnel::class, 'personnel_guests')
            ->withPivot('notes')
            ->withTimestamps();
    }

    /**
     * آیا این مهمان از نوع بانکی است؟
     */
    public function isBankAffiliated(): bool
    {
        return Personnel::isFamilyMemberBankAffiliated($this->relation);
    }

    /**
     * نمایش badge برای نوع مهمان
     */
    public function getRelationBadgeClass(): string
    {
        return $this->isBankAffiliated() ? 'success' : 'warning';
    }

    /**
     * نمایش متن badge
     */
    public function getRelationBadgeText(): string
    {
        return $this->isBankAffiliated() ? 'بانکی' : 'متفرقه';
    }

    /**
     * جستجو بر اساس کد ملی
     */
    public static function findByNationalCode(string $nationalCode): ?self
    {
        return static::where('national_code', $nationalCode)->first();
    }

    /**
     * ایجاد یا به‌روزرسانی مهمان
     */
    public static function createOrUpdate(array $data): self
    {
        return static::updateOrCreate(
            ['national_code' => $data['national_code']],
            $data
        );
    }
}
