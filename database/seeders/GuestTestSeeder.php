<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Personnel;
use Illuminate\Database\Seeder;

class GuestTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('ایجاد مهمانان نمونه...');

        // پیدا کردن اولین پرسنل
        $personnel = Personnel::first();

        if (!$personnel) {
            $this->command->warn('هیچ پرسنلی یافت نشد. ابتدا PersonnelSeeder را اجرا کنید.');
            return;
        }

        // ساخت مهمانان بانکی
        $bankGuests = [
            [
                'national_code' => '0123456789',
                'full_name' => 'فاطمه احمدی',
                'relation' => 'همسر',
                'gender' => 'female',
                'birth_date' => '1370-05-15',
                'phone' => '09121234567',
            ],
            [
                'national_code' => '0123456790',
                'full_name' => 'علی احمدی',
                'relation' => 'فرزند',
                'gender' => 'male',
                'birth_date' => '1395-03-20',
            ],
            [
                'national_code' => '0123456791',
                'full_name' => 'مریم محمدی',
                'relation' => 'مادر',
                'gender' => 'female',
                'birth_date' => '1345-08-10',
            ],
        ];

        // ساخت مهمانان متفرقه
        $miscGuests = [
            [
                'national_code' => '0123456792',
                'full_name' => 'رضا کریمی',
                'relation' => 'دوست',
                'gender' => 'male',
                'birth_date' => '1368-12-05',
                'phone' => '09129876543',
            ],
            [
                'national_code' => '0123456793',
                'full_name' => 'زهرا رضایی',
                'relation' => 'فامیل',
                'gender' => 'female',
                'birth_date' => '1375-07-25',
            ],
        ];

        // ایجاد و اتصال مهمانان بانکی
        foreach ($bankGuests as $guestData) {
            $guest = Guest::create($guestData);
            $personnel->guests()->attach($guest->id, [
                'notes' => 'مهمان نمونه - بانکی'
            ]);
            $this->command->info("✅ {$guest->full_name} ({$guest->relation}) - بانکی");
        }

        // ایجاد و اتصال مهمانان متفرقه
        foreach ($miscGuests as $guestData) {
            $guest = Guest::create($guestData);
            $personnel->guests()->attach($guest->id, [
                'notes' => 'مهمان نمونه - متفرقه'
            ]);
            $this->command->info("✅ {$guest->full_name} ({$guest->relation}) - متفرقه");
        }

        // نمایش خلاصه
        $this->command->newLine();
        $this->command->info("📊 خلاصه:");
        $this->command->info("  پرسنل: {$personnel->full_name}");
        $this->command->info("  مهمانان بانکی: " . $personnel->getBankAffiliatedGuestsCount());
        $this->command->info("  مهمانان متفرقه: " . $personnel->getNonBankAffiliatedGuestsCount());
        $this->command->info("  جمع کل: " . $personnel->guests()->count());

        $this->command->newLine();
        $this->command->info('✅ Seeder با موفقیت اجرا شد!');
    }
}
