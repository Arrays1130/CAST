<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! $this->shouldSeedDemo()) {
            return;
        }

        if (User::query()->exists()) {
            return;
        }

        User::query()->firstOrCreate(
            ['email' => 'briel@ilinkcst.edu.ph'],
            [
                'name' => 'Briel',
                'password' => 'iloveyouILINK',
                'role' => 'teacher',
                'email_verified_at' => now(),
                'must_change_password' => true,
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

    protected function shouldSeedDemo(): bool
    {
        if (filter_var(config('cast.seed_demo', false), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        return app()->environment('local', 'testing');
    }
}
