<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'az', 'name' => 'Azerbaijani', 'native_name' => 'Azərbaycan', 'is_default' => true, 'position' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_default' => false, 'position' => 2],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'is_default' => false, 'position' => 3],
        ];

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(
                ['code' => $language['code']],
                $language,
            );
        }

        Language::flushCache();
    }
}