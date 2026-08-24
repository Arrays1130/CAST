<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaperVersion extends Model
{
    protected $fillable = [
        'paper_id',
        'file_path',
        'original_filename',
        'drive_url',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }
}
