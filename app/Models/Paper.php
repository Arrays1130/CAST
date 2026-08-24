<?php

namespace App\Models;

use App\Enums\PaperStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paper extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'group_name',
        'status',
        'file_path',
        'original_filename',
        'drive_url',
        'submitted_at',
        'due_at',
        'tags',
        'score',
        'remarks',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaperStatus::class,
            'submitted_at' => 'datetime',
            'due_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PaperVersion::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isPast()
            && $this->status !== PaperStatus::Approved;
    }

    public function tagList(): array
    {
        if (blank($this->tags)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->tags))));
    }

    public function hasLocalFile(): bool
    {
        return filled($this->file_path);
    }

    public function isPdf(): bool
    {
        if (! $this->hasLocalFile()) {
            return false;
        }

        $name = strtolower((string) ($this->original_filename ?: $this->file_path));

        return str_ends_with($name, '.pdf');
    }

    public function hasDrive(): bool
    {
        return filled($this->drive_url);
    }

    public function drive(): ?\App\Support\GoogleDriveLink
    {
        return \App\Support\GoogleDriveLink::parse($this->drive_url);
    }

    public function sourceLabel(): string
    {
        if ($this->hasDrive() && $this->hasLocalFile()) {
            return 'File + Google Drive';
        }

        if ($this->hasDrive()) {
            return $this->drive()?->label() ?? 'Google Drive';
        }

        return $this->original_filename ?: 'Uploaded file';
    }
}
