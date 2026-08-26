<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_a_student_account(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->post(route('students.store'), [
                'name' => 'Ana Cruz',
                'email' => 'ana@school.test',
                'password' => 'TempCast1',
            ])
            ->assertRedirect()
            ->assertSessionHas('provisioned.password', 'TempCast1');

        $student = User::query()->where('email', 'ana@school.test')->first();

        $this->assertNotNull($student);
        $this->assertSame('student', $student->role);
        $this->assertTrue($student->hasVerifiedEmail());
        $this->assertTrue($student->must_change_password);
    }

    public function test_teacher_can_bulk_create_students(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->post(route('students.store'), [
                'password' => 'TempCast1',
                'roster' => "Ana Cruz, ana@school.test\nBen Santos | ben@school.test",
            ])
            ->assertRedirect();

        $this->assertSame(2, User::query()->where('role', 'student')->count());
    }

    public function test_student_cannot_provision_accounts(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('students.store'), [
                'name' => 'Nope',
                'email' => 'nope@school.test',
                'password' => 'TempCast1',
            ])
            ->assertForbidden();
    }

    public function test_provisioned_student_must_change_password_before_papers(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'must_change_password' => true,
            'password' => 'TempCast1',
        ]);

        $this->actingAs($student)
            ->get(route('papers.index'))
            ->assertRedirect(route('password.change'));

        $this->actingAs($student)
            ->put(route('password.change.update'), [
                'password' => 'NewCast99',
                'password_confirmation' => 'NewCast99',
            ])
            ->assertRedirect(route('papers.index'));

        $this->assertFalse($student->fresh()->must_change_password);

        $this->actingAs($student->fresh())
            ->get(route('papers.index'))
            ->assertOk();
    }

    public function test_teacher_can_reset_student_password(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'email' => 'resetme@school.test']);

        $this->actingAs($teacher)
            ->post(route('students.password', $student), [
                'password' => 'FreshTemp1',
            ])
            ->assertRedirect()
            ->assertSessionHas('provisioned.password', 'FreshTemp1');

        $this->assertTrue($student->fresh()->must_change_password);
    }
}
