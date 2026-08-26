<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ProvisionSchoolStudentsCommand extends Command
{
    protected $signature = 'cast:provision-school-students';

    protected $description = 'Create CAST school student and adviser logins. They must change password on first login.';

    public function handle(): int
    {
        $password = (string) config('cast.student_temp_password');
        $created = 0;
        $skipped = 0;

        foreach (config('cast.provisioned_teachers', []) as $row) {
            [$ok, $skip] = $this->provision($row, 'teacher', $password);
            $created += $ok;
            $skipped += $skip;
        }

        foreach (config('cast.provisioned_students', []) as $row) {
            [$ok, $skip] = $this->provision($row, 'student', $password);
            $created += $ok;
            $skipped += $skip;
        }

        $this->info("Done. Created/updated {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * @param  array{name?: string, email?: string}  $row
     * @return array{0: int, 1: int}
     */
    private function provision(array $row, string $role, string $password): array
    {
        $email = strtolower((string) ($row['email'] ?? ''));
        $name = trim((string) ($row['name'] ?? ''));

        if ($email === '' || $name === '') {
            return [0, 0];
        }

        $existing = User::query()->where('email', $email)->first();

        if ($existing === null && $role === 'teacher') {
            $existing = User::query()
                ->where('role', 'teacher')
                ->where('email', 'sir@cast.test')
                ->first();
        }

        if ($existing) {
            if ($existing->role === $role && $existing->must_change_password) {
                $existing->update([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'email_verified_at' => $existing->email_verified_at ?? now(),
                ]);
                $this->info('Updated '.$email);
                return [1, 0];
            }

            if ($existing->email === 'sir@cast.test' && $role === 'teacher') {
                $existing->update([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'teacher',
                    'email_verified_at' => now(),
                    'must_change_password' => true,
                ]);
                $this->info('Replaced sir@cast.test with '.$email);
                return [1, 0];
            }

            $this->line('Skip '.$email);
            return [0, 1];
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'email_verified_at' => now(),
            'must_change_password' => true,
        ]);

        $this->info('Created '.$email);

        return [1, 0];
    }
}
