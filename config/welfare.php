<?php

return [
    /*
    |--------------------------------------------------------------------------
    | قانون ۳ سال
    |--------------------------------------------------------------------------
    */
    'three_year_rule_days' => env('THREE_YEAR_RULE_DAYS', 1095),

    /*
    |--------------------------------------------------------------------------
    | تقویم قرعه‌کشی
    |--------------------------------------------------------------------------
    */
    'lottery' => [
        'registration_start_day' => env('LOTTERY_REGISTRATION_START_DAY', 1),
        'registration_end_day' => env('LOTTERY_REGISTRATION_END_DAY', 14),
        'draw_day' => env('LOTTERY_DRAW_DAY', 15),
        'provincial_approval_timeout_hours' => 48,
    ],

    /*
    |--------------------------------------------------------------------------
    | الگوریتم امتیازدهی
    |--------------------------------------------------------------------------
    */
    'priority_score' => [
        'base_score' => 100,
        'days_since_last_use_multiplier' => 0.1,
        'service_years_multiplier' => 0.5,
        'usage_penalty_multiplier' => 5,
        'family_match_bonus' => 10,
        'never_used_bonus' => 50,
        'isargar_bonus' => 30,
        'random_max' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | تعرفه‌ها (ریال)
    |--------------------------------------------------------------------------
    */
    'tariffs' => [
        'bank_rate' => [
            'accommodation_per_night' => 2000000, // کل خانواده
            'breakfast' => 190000,
            'lunch' => 625000,
            'dinner' => 530000,
        ],
        'free_bank_rate' => [
            'accommodation_per_night' => 1950000, // هر نفر
            'breakfast' => 520000,
            'lunch' => 1690000,
            'dinner' => 1430000,
        ],
        'free_non_bank_rate' => [
            'accommodation_per_night' => 3900000, // هر نفر
            'breakfast' => 600000,
            'lunch' => 1950000,
            'dinner' => 1650000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | تخفیفات
    |--------------------------------------------------------------------------
    */
    'discounts' => [
        'isargar' => 50, // درصد
        'never_used_20_years' => 50, // درصد
        'off_peak_free_accommodation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | مراکز رفاهی
    |--------------------------------------------------------------------------
    */
    'centers' => [
        'mashhad' => [
            'name' => 'زائرسرای مشهد مقدس',
            'city' => 'مشهد',
            'type' => 'religious',
            'stay_duration' => 5,
            'check_in_time' => '09:00',
            'check_out_time' => '09:00',
            'active_from_month' => 12, // اسفند
            'active_from_day' => 28,
            'active_to_month' => 11, // بهمن
            'overhaul_month' => 12, // اسفند
        ],
        'babolsar' => [
            'name' => 'متل بابلسر',
            'city' => 'بابلسر',
            'type' => 'beach',
            'stay_duration' => 4,
            'check_in_time' => '09:00',
            'check_out_time' => '09:00',
            'active_from_month' => 12,
            'active_from_day' => 28,
            'active_to_month' => 11,
            'overhaul_months' => [11, 12], // بهمن و اسفند
        ],
        'chadegan' => [
            'name' => 'موتل چادگان',
            'city' => 'چادگان',
            'type' => 'mountain',
            'stay_duration' => 3,
            'check_in_time' => '17:00',
            'check_out_time' => '09:00',
            'active_from_month' => 12,
            'active_from_day' => 28,
            'active_to_month' => 10, // دی
            'overhaul_months' => [10, 11, 12], // دی، بهمن، اسفند
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | فصل‌ها
    |--------------------------------------------------------------------------
    */
    'seasons' => [
        'golden_peak' => [
            'name' => 'پیک طلایی',
            'discount' => 0,
        ],
        'peak' => [
            'name' => 'پیک',
            'discount' => 0,
        ],
        'mid_season' => [
            'name' => 'میان‌فصل',
            'discount' => 0,
        ],
        'off_peak' => [
            'name' => 'غیرپیک',
            'discount' => 0,
        ],
        'super_off_peak' => [
            'name' => 'فوق‌العاده غیرپیک',
            'discount' => 100, // رایگان
        ],
    ],
];
