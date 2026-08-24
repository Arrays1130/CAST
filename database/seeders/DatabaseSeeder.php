<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::query()->exists()) {
            return;
        }

        User::query()->firstOrCreate(
            ['email' => 'sir@cast.test'],
            [
                'name' => 'Sir',
                'password' => 'password',
                'role' => 'teacher',
                'email_verified_at' => now(),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'student@cast.test'],
            [
                'name' => 'Sample Student',
                'password' => 'password',
                'role' => 'student',
                'email_verified_at' => now(),
            ]
        );
    }
}
