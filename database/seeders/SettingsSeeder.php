<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['company_name', 'CV. JAYA PAKAN UTAMA', 'string'],
            ['bank_name', 'BCA', 'string'],
            ['bank_account_number', '123456789', 'string'],
            ['bank_account_name', 'CV. JAYA PAKAN UTAMA', 'string'],
            ['apriori_min_support', '0.20', 'float'],
            ['apriori_min_confidence', '0.60', 'float'],
            ['apriori_min_lift', '1.0', 'float'],
            ['apriori_max_itemset', '3', 'integer'],
        ];

        foreach ($settings as [$key, $value, $type]) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => $type]
            );
        }
    }
}
