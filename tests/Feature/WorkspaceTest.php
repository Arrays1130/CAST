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

    public function test_teacher_can_view_pdf_inline_and_download(): void
    {
        Storage::fake('papers');
        Storage::disk('papers')->put('manuscript.pdf', '%PDF-1.4 fake');

        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Manuscript',
            'status' => 'submitted',
            'file_path' => 'manuscript.pdf',
            'original_filename' => 'manuscript.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('papers.view', $paper))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($teacher)
            ->get(route('papers.download', $paper))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('papers.show', $paper))
            ->assertOk()
            ->assertSee('PDF preview', false)
            ->assertSee(route('papers.view', $paper), false)
            ->assertSee('Download', false);
    }

    public function test_docx_cannot_be_viewed_inline_but_can_download(): void
    {
        Storage::fake('papers');
        Storage::disk('papers')->put('chapter.docx', 'fake-docx');

        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Word draft',
            'status' => 'submitted',
            'file_path' => 'chapter.docx',
            'original_filename' => 'chapter.docx',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('papers.view', $paper))
            ->assertNotFound();

        $this->actingAs($teacher)
            ->get(route('papers.download', $paper))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('papers.show', $paper))
            ->assertOk()
            ->assertSee('can’t be previewed', false)
            ->assertDontSee('PDF preview', false);
    }

    public function test_drive_paper_still_shows_preview(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);
        $paper = Paper::create([
            'user_id' => $student->id,
            'title' => 'Drive manuscript',
            'status' => 'submitted',
            'file_path' => '',
            'original_filename' => '',
            'drive_url' => 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUvWxYz/view?usp=sharing',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('papers.show', $paper))
            ->assertOk()
            ->assertSee('Preview', false)
            ->assertSee('drive.google.com', false)
            ->assertSee('Open in Drive', false);
    }

    public function test_teacher_can_filter_queue_and_sees_comment_counts(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student']);

        $review = Paper::create([
            'user_id' => $student->id,
            'title' => 'Needs eyes',
            'status' => 'for_review',
            'file_path' => 'a.pdf',
            'original_filename' => 'a.pdf',
            'submitted_at' => now()->subDay(),
            'due_at' => now()->subDays(2),
        ]);
        $review->comments()->create([
            'user_id' => $student->id,
            'body' => 'Ready for check',
        ]);

        Paper::create([
            'user_id' => $student->id,
            'title' => 'Already approved',
            'status' => 'approved',
            'file_path' => 'b.pdf',
            'original_filename' => 'b.pdf',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('papers.index', ['filter' => 'for_review']))
            ->assertOk()
            ->assertSee('Needs eyes')
            ->assertDontSee('Already approved');

        $this->actingAs($teacher)
            ->get(route('papers.index', ['filter' => 'overdue']))
            ->assertOk()
            ->assertSee('Needs eyes');
    }

    public function test_teacher_can_update_status_with_optional_comment(): void
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

        $this->actingAs($teacher)
            ->put(route('papers.status.update', $paper), [
                'status' => 'needs_revision',
                'comment' => 'Please fix methodology.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('papers', [
            'id' => $paper->id,
            'status' => 'needs_revision',
        ]);
        $this->assertDatabaseHas('comments', [
            'paper_id' => $paper->id,
            'body' => 'Please fix methodology.',
        ]);

        $this->actingAs($teacher)
            ->get(route('papers.show', $paper))
            ->assertOk()
            ->assertSee('Review', false)
            ->assertSee('Save score &amp; details', false);
    }

    public function test_updates_unread_filter_and_mark_read_can_stay(): void
    {
        $user = User::factory()->create();
        $paper = Paper::create([
            'user_id' => $user->id,
            'title' => 'Chapter work',
            'status' => 'submitted',
            'file_path' => '',
            'original_filename' => '',
            'submitted_at' => now(),
        ]);
        $notice = Notice::create([
            'user_id' => $user->id,
            'paper_id' => $paper->id,
            'message' => 'Status of “Chapter work” is now For review.',
        ]);
        Notice::create([
            'user_id' => $user->id,
            'paper_id' => $paper->id,
            'message' => 'Old note that should hide',
            'read_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('notices.index', ['unread' => 1]))
            ->assertOk()
            ->assertSee('Chapter work')
            ->assertSee('Status')
            ->assertDontSee('Old note that should hide');

        $this->actingAs($user)
            ->post(route('notices.read', $notice), ['stay' => 1])
            ->assertRedirect();

        $this->assertNotNull($notice->fresh()->read_at);
    }
}
