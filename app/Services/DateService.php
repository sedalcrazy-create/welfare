<?php

namespace App\Services;

use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class DateService
{
    /**
     * تبدیل تاریخ شمسی به میلادی
     */
    public function jalaliToGregorian(string $jalaliDate): string
    {
        $parts = explode('/', $jalaliDate);
        if (count($parts) !== 3) {
            return now()->format('Y-m-d');
        }

        $jalalian = new Jalalian((int)$parts[0], (int)$parts[1], (int)$parts[2]);
        return $jalalian->toCarbon()->format('Y-m-d');
    }

    /**
     * تبدیل تاریخ شمسی به Carbon
     */
    public function jalaliToCarbon(string $jalaliDate): Carbon
    {
        $parts = explode('/', $jalaliDate);
        if (count($parts) !== 3) {
            return now();
        }

        $jalalian = new Jalalian((int)$parts[0], (int)$parts[1], (int)$parts[2]);
        return $jalalian->toCarbon();
    }

    /**
     * تبدیل تاریخ میلادی به شمسی
     */
    public function gregorianToJalali(string $gregorianDate, string $format = 'Y/m/d'): string
    {
        return Jalalian::fromCarbon(Carbon::parse($gregorianDate))->format($format);
    }

    /**
     * بررسی معتبر بودن تاریخ شمسی
     */
    public function isValidJalaliDate(string $jalaliDate): bool
    {
        $parts = explode('/', $jalaliDate);
        if (count($parts) !== 3) {
            return false;
        }

        try {
            new Jalalian((int)$parts[0], (int)$parts[1], (int)$parts[2]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
