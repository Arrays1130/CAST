<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherInviteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('papers.index')
        : view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('papers.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/teachers/promote', [TeacherInviteController::class, 'promote'])
        ->middleware(['teacher', 'throttle:10,1'])
        ->name('teachers.promote');

    Route::get('/updates', [NoticeController::class, 'index'])->name('notices.index');
    Route::post('/updates/read-all', [NoticeController::class, 'markAllRead'])->name('notices.read-all');
    Route::post('/updates/{notice}', [NoticeController::class, 'markRead'])->name('notices.read');

    Route::get('/students', [StudentController::class, 'index'])->middleware('teacher')->name('students.index');
    Route::post('/students', [StudentController::class, 'store'])
        ->middleware(['teacher', 'throttle:30,1'])
        ->name('students.store');
    Route::post('/students/{user}/password', [StudentController::class, 'resetPassword'])
        ->middleware(['teacher', 'throttle:30,1'])
        ->name('students.password');
    Route::post('/students/{user}/verify', [StudentController::class, 'verify'])
        ->middleware(['teacher', 'throttle:30,1'])
        ->name('students.verify');
    Route::get('/papers', [PaperController::class, 'index'])->name('papers.index');
    Route::get('/papers/export', [PaperController::class, 'export'])->middleware('teacher')->name('papers.export');
    Route::get('/papers/create', [PaperController::class, 'create'])->middleware('student')->name('papers.create');
    Route::post('/papers', [PaperController::class, 'store'])->middleware(['student', 'throttle:20,1'])->name('papers.store');
    Route::get('/papers/{paper}', [PaperController::class, 'show'])->name('papers.show');
    Route::put('/papers/{paper}', [PaperController::class, 'updateDetails'])->middleware('throttle:30,1')->name('papers.update');
    Route::post('/papers/{paper}/archive', [PaperController::class, 'archive'])->middleware('throttle:20,1')->name('papers.archive');
    Route::get('/papers/{paper}/download', [PaperController::class, 'download'])->name('papers.download');
    Route::get('/papers/{paper}/view', [PaperController::class, 'viewFile'])->name('papers.view');
    Route::post('/papers/{paper}/scan-references', [PaperController::class, 'scanReferences'])
        ->middleware('throttle:10,1')
        ->name('papers.scan-references');
    Route::get('/papers/{paper}/versions/{version}', [PaperController::class, 'downloadVersion'])->name('papers.versions.download');
    Route::put('/papers/{paper}/file', [PaperController::class, 'updateFile'])->middleware(['student', 'throttle:15,1'])->name('papers.file.update');
    Route::put('/papers/{paper}/status', [PaperController::class, 'updateStatus'])->middleware(['teacher', 'throttle:30,1'])->name('papers.status.update');
    Route::post('/papers/{paper}/comments', [CommentController::class, 'store'])->middleware('throttle:30,1')->name('papers.comments.store');
});

require __DIR__.'/auth.php';
