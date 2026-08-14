<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    protected $fillable = [
        'folder_id',
        'disk',
        'path',
        'original_name',
        'name',
        'mime_type',
        'extension',
        'size_bytes',
        'width',
        'height',
        'alt_text',
        'public_token',
        'public_enabled',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'public_enabled' => 'boolean',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /** Value suitable for admin form path fields (/storage/...). */
    public function formValue(): string
    {
        $url = $this->url();

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $path = parse_url($url, PHP_URL_PATH);

            return is_string($path) && $path !== '' ? $path : $url;
        }

        return $url;
    }

    public function isVideo(): bool
    {
        return str_starts_with((string) $this->mime_type, 'video/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf' || $this->extension === 'pdf';
    }

    public function humanSize(): string
    {
        $bytes = max(0, (int) $this->size_bytes);

        if ($bytes < 1024) {
            return $bytes.' بايت';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' ك.ب';
        }

        return number_format($bytes / (1024 * 1024), 1).' م.ب';
    }

    public function aspectRatioLabel(): string
    {
        $w = (int) ($this->width ?? 0);
        $h = (int) ($this->height ?? 0);

        if ($w < 1 || $h < 1) {
            return '—';
        }

        $a = $w;
        $b = $h;
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return ($w / $a).':'.($h / $a);
    }

    public function publicShareUrl(): ?string
    {
        if (! $this->public_enabled || ! filled($this->public_token)) {
            return null;
        }

        return route('media.public', ['token' => $this->public_token]);
    }
}
