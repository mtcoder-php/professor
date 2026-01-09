<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'Professor Platform',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Tizim nomi'
            ],
            [
                'key' => 'app_description',
                'value' => 'Professor-O\'qituvchilarni Baholash Platformasi',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Tizim tavsifi'
            ],
            [
                'key' => 'admin_email',
                'value' => 'admin@platform.uz',
                'type' => 'email',
                'group' => 'general',
                'description' => 'Administrator email manzili'
            ],
            [
                'key' => 'support_phone',
                'value' => '+998901234567',
                'type' => 'text',
                'group' => 'general',
                'description' => 'Qo\'llab-quvvatlash telefon raqami'
            ],

            // Test Settings
            [
                'key' => 'test_duration_default',
                'value' => '60',
                'type' => 'number',
                'group' => 'test',
                'description' => 'Test davomiyligi (daqiqa, default)'
            ],
            [
                'key' => 'test_pass_score',
                'value' => '60',
                'type' => 'number',
                'group' => 'test',
                'description' => 'O\'tish bali (foiz, default)'
            ],
            [
                'key' => 'test_allow_retake',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'test',
                'description' => 'Testni qayta topshirish imkoni'
            ],
            [
                'key' => 'test_retake_delay',
                'value' => '24',
                'type' => 'number',
                'group' => 'test',
                'description' => 'Qayta topshirish uchun kutish vaqti (soat)'
            ],

            // Portfolio Settings
            [
                'key' => 'portfolio_max_file_size',
                'value' => '5',
                'type' => 'number',
                'group' => 'portfolio',
                'description' => 'Maksimal fayl hajmi (MB)'
            ],
            [
                'key' => 'portfolio_allowed_types',
                'value' => 'pdf,doc,docx',
                'type' => 'text',
                'group' => 'portfolio',
                'description' => 'Ruxsat etilgan fayl turlari'
            ],
            [
                'key' => 'portfolio_evaluation_deadline',
                'value' => '7',
                'type' => 'number',
                'group' => 'portfolio',
                'description' => 'Baholash muddati (kun)'
            ],

            // Notification Settings
            [
                'key' => 'notification_email_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Email bildirishnomalar yoqilgan'
            ],
            [
                'key' => 'notification_test_result',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Test natijasi haqida xabar'
            ],
            [
                'key' => 'notification_portfolio_evaluated',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'notification',
                'description' => 'Portfolio baholanganda xabar'
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
