<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password']
        );

        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (blank($adminEmail) && blank($adminPassword) && app()->environment(['local', 'testing'])) {
            // Development-only convenience credentials. Set your own in .env.
            $adminEmail = 'admin@example.com';
            $adminPassword = 'Admin@12345';
        }

        if (blank($adminEmail) || blank($adminPassword)) {
            throw new \RuntimeException(
                'Set both ADMIN_EMAIL and ADMIN_PASSWORD before seeding outside local/testing.'
            );
        }

        $admin = User::firstOrNew(['email' => $adminEmail]);
        $admin->name = env('ADMIN_NAME', 'Administrator');
        $admin->password = $adminPassword;
        $admin->forceFill([
            'is_admin' => true,
            'status' => 'active',
        ]);
        $admin->save();
    }
}
