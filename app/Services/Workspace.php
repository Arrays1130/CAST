<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Notice;
use App\Models\Paper;
use App\Models\PaperVersion;
use App\Models\User;

class Workspace
{
    public static function log(Paper $paper, User $actor, string $message): void
    {
        Activity::create([
            'paper_id' => $paper->id,
            'user_id' => $actor->id,
            'message' => $message,
        ]);
    }

    public static function notify(?User $user, Paper $paper, string $message): void
    {
        if (! $user || $user->id === auth()->id()) {
            return;
        }

        Notice::create([
            'user_id' => $user->id,
            'paper_id' => $paper->id,
            'message' => $message,
        ]);
    }

    public static function notifyTeachers(Paper $paper, string $message): void
    {
        User::query()->where('role', 'teacher')->get()
            ->each(fn (User $teacher) => self::notify($teacher, $paper, $message));
    }

    public static function snapshot(Paper $paper): void
    {
        if (! $paper->hasLocalFile() && ! $paper->hasDrive()) {
            return;
        }

        PaperVersion::create([
            'paper_id' => $paper->id,
            'file_path' => $paper->file_path ?: null,
            'original_filename' => $paper->original_filename,
            'drive_url' => $paper->drive_url,
        ]);
    }
}
