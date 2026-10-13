<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'برنامه‌نویسی',
                'children' => [
                    ['name' => 'PHP'],
                    ['name' => 'Laravel'],
                    ['name' => 'JavaScript'],
                    ['name' => 'Next.js'],
                ],
            ],
            [
                'name' => 'طراحی',
                'children' => [
                    ['name' => 'UI/UX'],
                    ['name' => 'گرافیک'],
                ],
            ],
            [
                'name' => 'بازاریابی',
                'children' => [
                    ['name' => 'سئو'],
                    ['name' => 'شبکه‌های اجتماعی'],
                ],
            ],
        ];

        foreach ($categories as $cat) {
            $parent = Category::create([
                'name'      => $cat['name'],
                'is_active' => true,
            ]);

            foreach ($cat['children'] ?? [] as $child) {
                Category::create([
                    'name'      => $child['name'],
                    'parent_id' => $parent->id,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('✅ دسته‌بندی‌های پیش‌فرض ساخته شدند');
    }
}