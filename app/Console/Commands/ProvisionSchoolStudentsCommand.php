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

        if ($password === '') {
            $this->error('CAST_STUDENT_TEMP_PASSWORD / cast.student_temp_password is empty.');

            return self::FAILURE;
        }

        foreach (config('cast.provisioned_teachers', []) as $row) {
            $created += $this->provision($row, 'teacher', $password);
        }

        foreach (config('cast.provisioned_students', []) as $row) {
            $created += $this->provision($row, 'student', $password);
        }

        $this->info("Done. Synced {$created} school login(s).");

        return self::SUCCESS;
    }

    /**
     * @param  array{name?: string, email?: string}  $row
     */
    private function provision(array $row, string $role, string $password): int
    {
        $email = strtolower(trim((string) ($row['email'] ?? '')));
        $name = trim((string) ($row['name'] ?? ''));

        if ($email === '' || $name === '') {
            return 0;
        }

        $existing = User::query()->where('email', $email)->first();

        if ($existing === null && $role === 'teacher') {
            $existing = User::query()
                ->where('role', 'teacher')
                ->where('email', 'sir@cast.test')
                ->first();
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'email_verified_at' => $existing?->email_verified_at ?? now(),
            'must_change_password' => true,
        ];

        if ($existing) {
            $existing->fill($payload);
            $existing->save();
            $this->info('Synced '.$email.' ('.$role.')');

            return 1;
        }

        User::create($payload);
        $this->info('Created '.$email.' ('.$role.')');

        return 1;
    }
}
