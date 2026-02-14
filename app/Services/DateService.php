<?php

namespace App\Services;

use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class DateService
{
    /**
     * تبدیل اعداد فارسی به انگلیسی
     */
    private function convertPersianToEnglish(string $string): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($persian, $english, $string);
    }

    /**
     * تبدیل تاریخ شمسی به میلادی
     */
    public function jalaliToGregorian(string $jalaliDate): string
    {
        // تبدیل اعداد فارسی به انگلیسی
        $jalaliDate = $this->convertPersianToEnglish($jalaliDate);

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
        // تبدیل اعداد فارسی به انگلیسی
        $jalaliDate = $this->convertPersianToEnglish($jalaliDate);

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
        // تبدیل اعداد فارسی به انگلیسی
        $jalaliDate = $this->convertPersianToEnglish($jalaliDate);

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
