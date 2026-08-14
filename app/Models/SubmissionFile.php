<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SubmissionFile extends Model
{
    protected $fillable = [
        'assignment_submission_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(AssignmentSubmission::class, 'assignment_submission_id');
    }

    public function downloadUrl(): ?string
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }

        return null;
    }

    public function formattedSize(): ?string
    {
        if (! $this->file_size) {
            return null;
        }

        if ($this->file_size >= 1048576) {
            return round($this->file_size / 1048576, 1).' MB';
        }

        return round($this->file_size / 1024).' KB';
    }
}
