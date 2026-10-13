<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ادمین اصلی
        $admin = User::updateOrCreate(
            ['phone' => '09120000000'],
            [
                'first_name'        => 'مدیر',
                'last_name'         => 'سیستم',
                'email'             => 'admin@example.com',
                'password'          => Hash::make('admin123456'),
                'role'              => 'admin',
                'status'            => 'active',
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // یه ادیتور نمونه
        $editor = User::updateOrCreate(
            ['phone' => '09121111111'],
            [
                'first_name'        => 'نویسنده',
                'last_name'         => 'محتوا',
                'password'          => Hash::make('editor123456'),
                'role'              => 'editor',
                'status'            => 'active',
                'phone_verified_at' => now(),
            ]
        );
        $editor->assignRole('editor');

        // یه کاربر عادی نمونه
        $user = User::updateOrCreate(
            ['phone' => '09122222222'],
            [
                'first_name'        => 'کاربر',
                'last_name'         => 'عادی',
                'role'              => 'user',
                'status'            => 'active',
                'phone_verified_at' => now(),
            ]
        );
        $user->assignRole('user');

        $this->command->info('✅ کاربران پیش‌فرض ساخته شدند');
        $this->command->info('👤 ادمین: 09120000000 / پسورد: admin123456');
        $this->command->info('👤 ادیتور: 09121111111 / پسورد: editor123456');
        $this->command->info('👤 کاربر:  09122222222 / بدون پسورد (OTP)');
    }
}