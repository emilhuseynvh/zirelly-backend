<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@zirelly.com');

        User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'surname' => 'Zirelly',
                'password' => env('ADMIN_PASSWORD', 'Admin123!'),
            ],
        )->forceFill([
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ])->save();
    }
}