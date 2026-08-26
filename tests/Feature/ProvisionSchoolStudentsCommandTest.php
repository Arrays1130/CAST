<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvisionSchoolStudentsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_school_students_once(): void
    {
        $this->artisan('cast:provision-school-students')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'email' => 'joshua@ilinkcst.edu.ph',
            'role' => 'student',
            'must_change_password' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'zuharto_mangagel201309206@ilinkcst.edu.ph',
            'role' => 'student',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'norhaida@ilinkcst.edu.ph',
            'role' => 'student',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'eleonor@ilinkcst.edu.ph',
            'role' => 'student',
            'must_change_password' => true,
        ]);

        $joshua = User::query()->where('email', 'joshua@ilinkcst.edu.ph')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('iloveyouILINK', $joshua->password));

        $this->artisan('cast:provision-school-students')
            ->assertSuccessful();

        $this->assertSame(4, User::query()->where('role', 'student')->where('email', 'like', '%@ilinkcst.edu.ph')->count());
    }
}
