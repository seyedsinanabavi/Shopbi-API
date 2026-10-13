<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // عمومی
            ['site_name',        'سایت من',                 'string',  'general', true],
            ['site_description', 'توضیحات سایت',            'text',    'general', true],
            ['site_logo',        null,                       'string',  'general', true],
            ['site_favicon',     null,                       'string',  'general', true],
            ['contact_email',    'info@example.com',         'string',  'general', true],
            ['contact_phone',    '02100000000',              'string',  'general', true],
            ['address',          'تهران',                    'text',    'general', true],

            // SEO
            ['meta_title',       'سایت من - بهترین‌ها',     'string',  'seo', true],
            ['meta_description', 'توضیحات پیش‌فرض سایت',    'text',    'seo', true],
            ['meta_keywords',    'سایت, بهترین',             'string',  'seo', true],
            ['google_analytics', null,                        'text',    'seo', true],
            ['google_search_console', null,                   'text',    'seo', false],

            // اجتماعی
            ['social_instagram', null, 'string', 'social', true],
            ['social_telegram',  null, 'string', 'social', true],
            ['social_twitter',   null, 'string', 'social', true],
            ['social_linkedin',  null, 'string', 'social', true],
            ['social_youtube',   null, 'string', 'social', true],

            // OTP / SMS
            ['otp_expire_minutes', '2',  'integer', 'sms', false],
            ['otp_length',         '5',  'integer', 'sms', false],
            ['otp_max_attempts',   '5',  'integer', 'sms', false],
            ['otp_resend_delay',   '60', 'integer', 'sms', false],

            // عمومی‌سازی (Maintenance)
            ['maintenance_mode', '0',    'boolean', 'system', true],
            ['maintenance_message', 'سایت در حال بروزرسانی است', 'text', 'system', true],
            ['allow_registration', '1',  'boolean', 'system', false],
            ['allow_comments',     '1',  'boolean', 'system', true],
        ];

        foreach ($settings as [$key, $value, $type, $group, $public]) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value'     => $value,
                    'type'      => $type,
                    'group'     => $group,
                    'is_public' => $public,
                ]
            );
        }

        $this->command->info('✅ تنظیمات پیش‌فرض ساخته شدند');
    }
}