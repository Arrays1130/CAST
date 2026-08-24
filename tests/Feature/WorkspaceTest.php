<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_submit_notifies_teacher_and_logs_activity(): void
    {
        Storage::fake('papers');

        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post('/papers', [
            'title' => 'Chapter 1',
            'tags' => 'draft, chapter 1',
            'due_at' => now()->addWeek()->toDateString(),
            'file' => UploadedFile::fake()->create('chapter-1.pdf', 120, 'application/pdf'),
        ])->assertRedirect();

        $this->assertDatabaseHas('papers', [
            'title' => 'Chapter 1',
            'tags' => 'draft, chapter 1',
            'user_id' => $student->id,
        ]);
        $this->assertDatabaseHas('activities', [
            'message' => 'Created this page and submitted a manuscript.',
        ]);
        $this->assertDatabaseHas('notices', [
            'user_id' => $teacher->id,
        ]);
    }

    public function test_teacher_comment_notifies_student(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Draft',
            'status' => 'submitted',
            'file_path' => 'draft.pdf',
            'original_filename' => 'draft.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)->post(route('papers.comments.store', $paper), [
            'body' => 'Please revise the methodology.',
        ])->assertRedirect();

        $this->assertDatabaseHas('notices', [
            'user_id' => $student->id,
            'paper_id' => $paper->id,
        ]);
    }

    public function test_teacher_can_export_csv_and_student_cannot(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get(route('papers.export'))->assertForbidden();
        $this->actingAs($teacher)->get(route('papers.export'))
            ->assertOk()
            ->assertHeader('content-disposition');
    }

    public function test_owner_can_archive_a_paper(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Old draft',
            'status' => 'submitted',
            'file_path' => 'old.pdf',
            'original_filename' => 'old.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->post(route('papers.archive', $paper))
            ->assertRedirect(route('papers.index', ['archived' => 1]));

        $this->assertNotNull($paper->fresh()->archived_at);
    }

    public function test_updates_inbox_is_visible(): void
    {
        $user = User::factory()->create();
        Notice::create([
            'user_id' => $user->id,
            'paper_id' => null,
            'message' => 'Welcome to CAST.',
        ]);

        $this->actingAs($user)
            ->get(route('notices.index'))
            ->assertOk()
            ->assertSee('Welcome to CAST.');
    }
}
