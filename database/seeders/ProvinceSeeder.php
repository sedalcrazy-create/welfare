<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            // استان‌ها
            ['name' => 'آذربایجان شرقی', 'code' => 'AZS', 'personnel_count' => 2500],
            ['name' => 'آذربایجان غربی', 'code' => 'AZG', 'personnel_count' => 1800],
            ['name' => 'اردبیل', 'code' => 'ARD', 'personnel_count' => 900],
            ['name' => 'اصفهان', 'code' => 'ESF', 'personnel_count' => 3500],
            ['name' => 'البرز', 'code' => 'ALB', 'personnel_count' => 1500],
            ['name' => 'ایلام', 'code' => 'ILM', 'personnel_count' => 500],
            ['name' => 'بوشهر', 'code' => 'BSH', 'personnel_count' => 700],
            ['name' => 'چهارمحال و بختیاری', 'code' => 'CHB', 'personnel_count' => 600],
            ['name' => 'خراسان جنوبی', 'code' => 'KHJ', 'personnel_count' => 600],
            ['name' => 'خراسان رضوی', 'code' => 'KHR', 'personnel_count' => 4000],
            ['name' => 'خراسان شمالی', 'code' => 'KHS', 'personnel_count' => 700],
            ['name' => 'خوزستان', 'code' => 'KHZ', 'personnel_count' => 3000],
            ['name' => 'زنجان', 'code' => 'ZNJ', 'personnel_count' => 800],
            ['name' => 'سمنان', 'code' => 'SMN', 'personnel_count' => 500],
            ['name' => 'سیستان و بلوچستان', 'code' => 'SBL', 'personnel_count' => 1500],
            ['name' => 'فارس', 'code' => 'FRS', 'personnel_count' => 3200],
            ['name' => 'قزوین', 'code' => 'QZV', 'personnel_count' => 900],
            ['name' => 'قم', 'code' => 'QOM', 'personnel_count' => 800],
            ['name' => 'کردستان', 'code' => 'KRD', 'personnel_count' => 1100],
            ['name' => 'کرمان', 'code' => 'KRM', 'personnel_count' => 2200],
            ['name' => 'کرمانشاه', 'code' => 'KSH', 'personnel_count' => 1400],
            ['name' => 'کهگیلویه و بویراحمد', 'code' => 'KBA', 'personnel_count' => 500],
            ['name' => 'گلستان', 'code' => 'GLS', 'personnel_count' => 1200],
            ['name' => 'گیلان', 'code' => 'GIL', 'personnel_count' => 1800],
            ['name' => 'لرستان', 'code' => 'LRS', 'personnel_count' => 1200],
            ['name' => 'مازندران', 'code' => 'MZN', 'personnel_count' => 2200],
            ['name' => 'مرکزی', 'code' => 'MRK', 'personnel_count' => 1000],
            ['name' => 'هرمزگان', 'code' => 'HRM', 'personnel_count' => 1200],
            ['name' => 'همدان', 'code' => 'HMD', 'personnel_count' => 1300],
            ['name' => 'یزد', 'code' => 'YZD', 'personnel_count' => 800],

            // ادارات امور تهران
            ['name' => 'شرق تهران', 'code' => 'TH1', 'personnel_count' => 4500, 'is_tehran' => true],
            ['name' => 'غرب تهران', 'code' => 'TH2', 'personnel_count' => 4800, 'is_tehran' => true],
            ['name' => 'شمال تهران', 'code' => 'TH3', 'personnel_count' => 3200, 'is_tehran' => true],
            ['name' => 'جنوب تهران', 'code' => 'TH4', 'personnel_count' => 3800, 'is_tehran' => true],
            ['name' => 'مرکز تهران', 'code' => 'TH5', 'personnel_count' => 4000, 'is_tehran' => true],
            ['name' => 'ستاد مرکزی', 'code' => 'TH6', 'personnel_count' => 2500, 'is_tehran' => true],
        ];

        // محاسبه کل پرسنل
        $totalPersonnel = array_sum(array_column($provinces, 'personnel_count'));

        foreach ($provinces as $province) {
            Province::create([
                'name' => $province['name'],
                'code' => $province['code'],
                'personnel_count' => $province['personnel_count'],
                'quota_ratio' => $province['personnel_count'] / $totalPersonnel,
                'is_tehran' => $province['is_tehran'] ?? false,
                'is_active' => true,
            ]);
        }
    }
}
