<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AcademicScheduleDocument extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(AcademicBatch::class, 'batch_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id');
    }

    public function scopeForStudent(Builder $query, AcademicStudent $student): Builder
    {
        $programId = $student->batch?->program_id;
        $batchId = $student->batch_id;

        return $query
            ->published()
            ->when($programId, fn (Builder $q) => $q->where('program_id', $programId), fn (Builder $q) => $q->whereRaw('1 = 0'))
            ->where(function (Builder $q) use ($batchId) {
                $q->whereNull('batch_id');
                if ($batchId) {
                    $q->orWhere('batch_id', $batchId);
                }
            });
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf'
            || str_ends_with(mb_strtolower((string) $this->original_name), '.pdf');
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}
