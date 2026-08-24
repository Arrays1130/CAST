<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    protected $fillable = [
        'user_id',
        'paper_id',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public function kindLabel(): string
    {
        $message = strtolower($this->message);

        if (str_contains($message, 'resubmitted') || str_contains($message, 'submitted')) {
            return 'Submission';
        }

        if (str_contains($message, 'commented')) {
            return 'Comment';
        }

        if (str_contains($message, 'status')) {
            return 'Status';
        }

        return 'Update';
    }
}
