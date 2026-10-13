<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== مجوزها ====================
        $permissions = [
            'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.publish',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'tags.view', 'tags.create', 'tags.edit', 'tags.delete',
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.ban',
            'comments.view', 'comments.approve', 'comments.delete',
            'settings.view', 'settings.edit',
            'media.upload', 'media.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ==================== نقش‌ها ====================
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
            'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.publish',
            'categories.view', 'categories.create', 'categories.edit',
            'tags.view', 'tags.create', 'tags.edit',
            'comments.view', 'comments.approve',
            'media.upload', 'media.delete',
        ]);

        $author = Role::firstOrCreate(['name' => 'author']);
        $author->syncPermissions([
            'posts.view', 'posts.create', 'posts.edit',
            'media.upload',
        ]);

        Role::firstOrCreate(['name' => 'user']);

        // ==================== کاربر ادمین ====================
        $adminUser = User::updateOrCreate(
            ['phone' => '09120000000'],
            [
                'first_name'        => 'مدیر',
                'last_name'         => 'سیستم',
                'email'             => 'admin@example.com',
                'password'          => Hash::make('admin123456'),
                'status'            => 'active',
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles(['admin']);

        $this->command->info('✅ نقش‌ها، مجوزها و ادمین ساخته شدند');
        $this->command->info('👤 ادمین: admin@example.com / admin123456');
    }
}