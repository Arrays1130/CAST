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

    public function test_teacher_can_promote_student(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'email' => 'to-promote@example.com']);

        $this->actingAs($teacher)
            ->post(route('teachers.promote'), ['email' => 'to-promote@example.com'])
            ->assertRedirect();

        $this->assertSame('teacher', $student->fresh()->role);
    }
}
