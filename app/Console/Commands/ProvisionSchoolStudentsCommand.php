<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ProvisionSchoolStudentsCommand extends Command
{
    protected $signature = 'cast:provision-school-students';

    protected $description = 'Create CAST student logins for school emails (skip existing). They must change password on first login.';

    public function handle(): int
    {
        $password = (string) config('cast.student_temp_password');
        $created = 0;
        $skipped = 0;

        foreach (config('cast.provisioned_students', []) as $row) {
            $email = strtolower((string) ($row['email'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));

            if ($email === '' || $name === '') {
                continue;
            }

            $existing = User::query()->where('email', $email)->first();
            if ($existing) {
                $skipped++;
                $this->line('Skip '.$email);
                continue;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'student',
                'email_verified_at' => now(),
                'must_change_password' => true,
            ]);

            $created++;
            $this->info('Created '.$email);
        }

        $this->info("Done. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
