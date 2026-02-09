<?php

namespace Database\Seeders;

use App\Models\Center;
use Illuminate\Database\Seeder;

class CenterSeeder extends Seeder
{
    public function run(): void
    {
        $centers = [
            [
                'name' => 'زائرسرای مشهد مقدس',
                'slug' => 'mashhad',
                'city' => 'مشهد',
                'type' => 'religious',
                'description' => 'زائرسرای بانک ملی در مشهد مقدس',
                'address' => 'مشهد، بلوار شهید صادقی',
                'phone' => '051-12345678',
                'bed_count' => 1029,
                'unit_count' => 227,
                'stay_duration' => 5,
                'check_in_time' => '09:00',
                'check_out_time' => '09:00',
                'amenities' => [
                    'parking', 'restaurant', 'prayer_room', 'wifi',
                    'laundry', 'clinic', 'elevator', 'wheelchair_access',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'متل بابلسر',
                'slug' => 'babolsar',
                'city' => 'بابلسر',
                'type' => 'beach',
                'description' => 'متل ساحلی بانک ملی در بابلسر',
                'address' => 'بابلسر، ساحل دریای خزر',
                'phone' => '011-12345678',
                'bed_count' => 626,
                'unit_count' => 165,
                'stay_duration' => 4,
                'check_in_time' => '09:00',
                'check_out_time' => '09:00',
                'amenities' => [
                    'parking', 'restaurant', 'beach_access', 'wifi',
                    'pool', 'playground', 'bbq_area',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'موتل چادگان',
                'slug' => 'chadegan',
                'city' => 'چادگان',
                'type' => 'mountain',
                'description' => 'موتل کوهستانی بانک ملی در چادگان',
                'address' => 'اصفهان، شهرستان چادگان',
                'phone' => '031-12345678',
                'bed_count' => 126,
                'unit_count' => 34,
                'stay_duration' => 3,
                'check_in_time' => '17:00',
                'check_out_time' => '09:00',
                'amenities' => [
                    'parking', 'kitchen', 'wifi', 'mountain_view',
                    'garden', 'bbq_area',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($centers as $center) {
            Center::create($center);
        }
    }
}
