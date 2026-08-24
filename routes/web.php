<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('papers.index')
        : view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('papers.index');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/updates', [NoticeController::class, 'index'])->name('notices.index');
    Route::post('/updates/read-all', [NoticeController::class, 'markAllRead'])->name('notices.read-all');
    Route::post('/updates/{notice}', [NoticeController::class, 'markRead'])->name('notices.read');

    Route::get('/papers', [PaperController::class, 'index'])->name('papers.index');
    Route::get('/papers/export', [PaperController::class, 'export'])->middleware('teacher')->name('papers.export');
    Route::get('/papers/create', [PaperController::class, 'create'])->middleware('student')->name('papers.create');
    Route::post('/papers', [PaperController::class, 'store'])->middleware('student')->name('papers.store');
    Route::get('/papers/{paper}', [PaperController::class, 'show'])->name('papers.show');
    Route::put('/papers/{paper}', [PaperController::class, 'updateDetails'])->name('papers.update');
    Route::post('/papers/{paper}/archive', [PaperController::class, 'archive'])->name('papers.archive');
    Route::get('/papers/{paper}/download', [PaperController::class, 'download'])->name('papers.download');
    Route::get('/papers/{paper}/view', [PaperController::class, 'viewFile'])->name('papers.view');
    Route::get('/papers/{paper}/versions/{version}', [PaperController::class, 'downloadVersion'])->name('papers.versions.download');
    Route::put('/papers/{paper}/file', [PaperController::class, 'updateFile'])->middleware('student')->name('papers.file.update');
    Route::put('/papers/{paper}/status', [PaperController::class, 'updateStatus'])->middleware('teacher')->name('papers.status.update');
    Route::post('/papers/{paper}/comments', [CommentController::class, 'store'])->name('papers.comments.store');
});

require __DIR__.'/auth.php';
