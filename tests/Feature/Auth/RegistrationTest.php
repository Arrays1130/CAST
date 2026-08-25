<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertSame('student', auth()->user()->role);
        $response->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_invite_code_registers_teacher(): void
    {
        config(['cast.teacher_invite_code' => 'secret-adviser']);

        $this->post('/register', [
            'name' => 'Adviser',
            'email' => 'adviser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite_code' => 'secret-adviser',
        ])->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticated();
        $this->assertSame('teacher', auth()->user()->role);
    }

    public function test_wrong_invite_code_stays_student(): void
    {
        config(['cast.teacher_invite_code' => 'secret-adviser']);

        $this->post('/register', [
            'name' => 'Student',
            'email' => 'student2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite_code' => 'wrong',
        ]);

        $this->assertSame('student', auth()->user()->role);
    }
}
