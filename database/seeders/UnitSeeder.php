<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        // زائرسرای مشهد
        $mashhad = Center::where('slug', 'mashhad')->first();
        $this->seedMashhadUnits($mashhad);

        // متل بابلسر
        $babolsar = Center::where('slug', 'babolsar')->first();
        $this->seedBabolsarUnits($babolsar);

        // موتل چادگان
        $chadegan = Center::where('slug', 'chadegan')->first();
        $this->seedChadeganUnits($chadegan);
    }

    private function seedMashhadUnits(Center $center): void
    {
        $units = [
            // ۲ تخته - ۱۶ عدد
            ['type' => 'room', 'bed_count' => 2, 'count' => 16, 'prefix' => 'R2'],
            // ۳ تخته - ۳۱ عدد
            ['type' => 'room', 'bed_count' => 3, 'count' => 31, 'prefix' => 'R3'],
            // ۴ تخته - ۶۶ عدد
            ['type' => 'room', 'bed_count' => 4, 'count' => 66, 'prefix' => 'R4'],
            // ۵ تخته - ۵۸ عدد (شامل سوئیت دوکله)
            ['type' => 'suite', 'bed_count' => 5, 'count' => 22, 'prefix' => 'S5', 'name_suffix' => ' (دوکله)'],
            ['type' => 'room', 'bed_count' => 5, 'count' => 36, 'prefix' => 'R5'],
            // ۶ تخته - ۴۹ عدد (شامل مدیریتی)
            ['type' => 'suite', 'bed_count' => 6, 'count' => 7, 'prefix' => 'M6', 'is_management' => true, 'name_suffix' => ' (مدیریتی وان‌دار)'],
            ['type' => 'suite', 'bed_count' => 6, 'count' => 6, 'prefix' => 'M6B', 'is_management' => true, 'name_suffix' => ' (مدیریتی دوخوابه)'],
            ['type' => 'room', 'bed_count' => 6, 'count' => 36, 'prefix' => 'R6'],
            // ۸ تخته - ۷ عدد (مدیریتی سه خوابه)
            ['type' => 'suite', 'bed_count' => 8, 'count' => 7, 'prefix' => 'M8', 'is_management' => true, 'name_suffix' => ' (مدیریتی سه خوابه)'],
        ];

        $number = 1;
        foreach ($units as $unitConfig) {
            for ($i = 1; $i <= $unitConfig['count']; $i++) {
                Unit::create([
                    'center_id' => $center->id,
                    'number' => $unitConfig['prefix'] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'اتاق ' . $number . ($unitConfig['name_suffix'] ?? ''),
                    'type' => $unitConfig['type'],
                    'bed_count' => $unitConfig['bed_count'],
                    'floor' => ceil($number / 30),
                    'is_management' => $unitConfig['is_management'] ?? false,
                    'status' => 'active',
                ]);
                $number++;
            }
        }
    }

    private function seedBabolsarUnits(Center $center): void
    {
        $units = [
            // ۲ تخته - ۲۰ عدد
            ['type' => 'villa', 'bed_count' => 2, 'count' => 20, 'prefix' => 'V2'],
            // ۳ تخته - ۳۸ عدد
            ['type' => 'villa', 'bed_count' => 3, 'count' => 38, 'prefix' => 'V3'],
            // ۴ تخته - ۷۷ عدد
            ['type' => 'villa', 'bed_count' => 4, 'count' => 69, 'prefix' => 'V4', 'name_suffix' => ' (محوطه ویلایی همکف)'],
            ['type' => 'apartment', 'bed_count' => 4, 'count' => 8, 'prefix' => 'A4'],
            // ۵ تخته - ۲۲ عدد
            ['type' => 'villa', 'bed_count' => 5, 'count' => 22, 'prefix' => 'V5'],
            // ۶ تخته - ۶ عدد
            ['type' => 'villa', 'bed_count' => 6, 'count' => 4, 'prefix' => 'V6', 'is_management' => true],
            ['type' => 'villa', 'bed_count' => 6, 'count' => 2, 'prefix' => 'M6'],
            // ۹ تخته - ۲ عدد
            ['type' => 'villa', 'bed_count' => 9, 'count' => 2, 'prefix' => 'V9'],
        ];

        $number = 1;
        foreach ($units as $unitConfig) {
            for ($i = 1; $i <= $unitConfig['count']; $i++) {
                Unit::create([
                    'center_id' => $center->id,
                    'number' => $unitConfig['prefix'] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'واحد ' . $number . ($unitConfig['name_suffix'] ?? ''),
                    'type' => $unitConfig['type'],
                    'bed_count' => $unitConfig['bed_count'],
                    'is_management' => $unitConfig['is_management'] ?? false,
                    'status' => 'active',
                ]);
                $number++;
            }
        }
    }

    private function seedChadeganUnits(Center $center): void
    {
        $units = [
            // ۲ تخته (۱ اتاقه) - ۶ عدد
            ['type' => 'villa', 'bed_count' => 2, 'count' => 6, 'prefix' => 'V1', 'name_suffix' => ' (یک اتاقه)'],
            // ۴ تخته (۲ اتاقه) - ۲۷ عدد
            ['type' => 'villa', 'bed_count' => 4, 'count' => 27, 'prefix' => 'V2', 'name_suffix' => ' (دو اتاقه)'],
            // ۶ تخته (۳ اتاقه) - ۱ عدد
            ['type' => 'villa', 'bed_count' => 6, 'count' => 1, 'prefix' => 'V3', 'name_suffix' => ' (سه اتاقه)'],
        ];

        $number = 1;
        foreach ($units as $unitConfig) {
            for ($i = 1; $i <= $unitConfig['count']; $i++) {
                Unit::create([
                    'center_id' => $center->id,
                    'number' => $unitConfig['prefix'] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'name' => 'ویلا ' . $number . ($unitConfig['name_suffix'] ?? ''),
                    'type' => $unitConfig['type'],
                    'bed_count' => $unitConfig['bed_count'],
                    'amenities' => ['kitchen', 'parking', 'bathroom', 'extra_mattress'],
                    'status' => 'active',
                ]);
                $number++;
            }
        }
    }
}
