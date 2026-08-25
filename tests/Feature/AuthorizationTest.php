<?php

namespace Tests\Feature;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_view_another_students_paper(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $owner->id,
            'title' => 'Private draft',
            'status' => 'submitted',
            'file_path' => 'private.pdf',
            'original_filename' => 'private.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($other)->get(route('papers.show', $paper))->assertForbidden();
        $this->actingAs($other)->get(route('papers.download', $paper))->assertForbidden();
        $this->actingAs($other)->get(route('papers.view', $paper))->assertForbidden();
    }

    public function test_teacher_can_view_student_paper(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Shared draft',
            'status' => 'submitted',
            'file_path' => '',
            'original_filename' => '',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)->get(route('papers.show', $paper))->assertOk();
    }

    public function test_guest_cannot_view_papers(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Hidden',
            'status' => 'submitted',
            'file_path' => '',
            'original_filename' => '',
            'submitted_at' => now(),
        ]);

        $this->get(route('papers.show', $paper))->assertRedirect(route('login'));
    }

    public function test_teacher_can_promote_verified_student_with_password(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'email' => 'to-promote@example.com']);

        $this->actingAs($teacher)
            ->post(route('teachers.promote'), [
                'email' => 'to-promote@example.com',
                'password' => 'password',
            ])
            ->assertRedirect();

        $this->assertSame('teacher', $student->fresh()->role);
    }

    public function test_teacher_cannot_promote_unverified_student(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->unverified()->create([
            'role' => 'student',
            'email' => 'pending@example.com',
        ]);

        $this->actingAs($teacher)
            ->from(route('profile.edit'))
            ->post(route('teachers.promote'), [
                'email' => 'pending@example.com',
                'password' => 'password',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame('student', $student->fresh()->role);
    }

    public function test_teacher_can_verify_pending_student(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->unverified()->create([
            'role' => 'student',
            'email' => 'pending-verify@school.test',
        ]);

        $this->actingAs($teacher)
            ->post(route('students.verify', $student))
            ->assertRedirect();

        $this->assertTrue($student->fresh()->hasVerifiedEmail());
    }

    public function test_student_cannot_verify_another_student(): void
    {
        $actor = User::factory()->create(['role' => 'student']);
        $pending = User::factory()->unverified()->create(['role' => 'student']);

        $this->actingAs($actor)
            ->post(route('students.verify', $pending))
            ->assertForbidden();

        $this->assertFalse($pending->fresh()->hasVerifiedEmail());
    }

    public function test_student_cannot_open_students_roster(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('students.index'))->assertForbidden();
    }

    public function test_teacher_can_view_students_roster(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        User::factory()->create(['role' => 'student', 'name' => 'Ana Capstone', 'email' => 'ana@school.test']);

        $this->actingAs($teacher)
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee('Ana Capstone')
            ->assertSee('ana@school.test');
    }

    public function test_student_cannot_change_due_date(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Chapter 1',
            'status' => 'submitted',
            'file_path' => '',
            'original_filename' => '',
            'due_at' => now()->addWeek(),
            'submitted_at' => now(),
        ]);

        $originalDue = $paper->due_at->toDateString();

        $this->actingAs($student)
            ->put(route('papers.update', $paper), [
                'title' => 'Chapter 1',
                'due_at' => now()->subWeek()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertSame($originalDue, $paper->fresh()->due_at->toDateString());
    }
}
