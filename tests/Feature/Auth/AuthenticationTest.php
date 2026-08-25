<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_chooser_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Who is logging in?')
            ->assertSee('Student log in')
            ->assertSee('Adviser log in');
    }

    public function test_student_and_adviser_login_screens_can_be_rendered(): void
    {
        $this->get(route('login.student'))
            ->assertOk()
            ->assertSee('Student log in')
            ->assertSee('Student portal');

        $this->get(route('login.adviser'))
            ->assertOk()
            ->assertSee('Adviser log in')
            ->assertSee('Adviser portal');
    }

    public function test_students_can_authenticate_on_student_portal(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'student',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('papers.index', absolute: false));
    }

    public function test_teachers_can_authenticate_on_adviser_portal(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('papers.index', absolute: false));
    }

    public function test_students_cannot_authenticate_on_adviser_portal(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->from(route('login.adviser'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'teacher',
        ])->assertSessionHasErrors('email')
            ->assertRedirect(route('login.adviser'));

        $this->assertGuest();
    }

    public function test_teachers_cannot_authenticate_on_student_portal(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $this->from(route('login.student'))->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'role' => 'student',
        ])->assertSessionHasErrors('email')
            ->assertRedirect(route('login.student'));

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
            'role' => 'student',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
