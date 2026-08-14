<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

class MediaLibraryService
{
    public const DISK = 'public';

    public const ROOT = 'media-library';

    /** @return array{files:int,images:int,storage_bytes:int,public_links:int} */
    public function stats(): array
    {
        return [
            'files' => (int) MediaAsset::query()->count(),
            'images' => (int) MediaAsset::query()->where('mime_type', 'like', 'image/%')->count(),
            'storage_bytes' => (int) MediaAsset::query()->sum('size_bytes'),
            'public_links' => (int) MediaAsset::query()->where('public_enabled', true)->count(),
        ];
    }

    public function createFolder(string $name, ?int $parentId, User $user): MediaFolder
    {
        $name = trim($name);

        if ($name === '') {
            throw ValidationException::withMessages(['folderName' => 'أدخل اسم المجلد.']);
        }

        if ($parentId) {
            MediaFolder::query()->findOrFail($parentId);
        }

        $slug = $this->uniqueFolderSlug($name, $parentId);

        return MediaFolder::query()->create([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => $slug,
            'created_by' => $user->id,
        ]);
    }

    public function renameFolder(MediaFolder $folder, string $name): MediaFolder
    {
        $name = trim($name);

        if ($name === '') {
            throw ValidationException::withMessages(['folderName' => 'أدخل اسم المجلد.']);
        }

        $folder->update([
            'name' => $name,
            'slug' => $this->uniqueFolderSlug($name, $folder->parent_id, $folder->id),
        ]);

        return $folder->fresh();
    }

    public function deleteFolder(MediaFolder $folder): void
    {
        DB::transaction(function () use ($folder): void {
            foreach ($folder->children()->get() as $child) {
                $this->deleteFolder($child);
            }

            foreach ($folder->assets()->get() as $asset) {
                $this->deleteAsset($asset);
            }

            $folder->delete();
        });
    }

    public function upload(TemporaryUploadedFile|UploadedFile $file, ?int $folderId, User $user): MediaAsset
    {
        if ($folderId) {
            MediaFolder::query()->findOrFail($folderId);
        }

        $original = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($original, PATHINFO_EXTENSION));
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $sizeBytes = $this->safeUploadSize($file);
        $safeBase = Str::slug(pathinfo($original, PATHINFO_FILENAME)) ?: 'file';
        $filename = $safeBase.'-'.Str::lower(Str::random(8)).($extension ? '.'.$extension : '');
        $dir = self::ROOT.($folderId ? '/'.$folderId : '/root');
        $path = $file->storeAs($dir, $filename, self::DISK);

        if (! $path) {
            throw ValidationException::withMessages(['uploads' => 'تعذّر رفع الملف.']);
        }

        if ($sizeBytes < 1 && Storage::disk(self::DISK)->exists($path)) {
            try {
                $sizeBytes = (int) Storage::disk(self::DISK)->size($path);
            } catch (Throwable) {
                $sizeBytes = 0;
            }
        }

        $absolute = Storage::disk(self::DISK)->path($path);
        $width = null;
        $height = null;

        if (str_starts_with($mime, 'image/') && is_file($absolute)) {
            $info = @getimagesize($absolute);
            if ($info !== false) {
                $width = (int) $info[0];
                $height = (int) $info[1];
            }
        }

        return MediaAsset::query()->create([
            'folder_id' => $folderId,
            'disk' => self::DISK,
            'path' => $path,
            'original_name' => $original,
            'name' => $original,
            'mime_type' => $mime,
            'extension' => $extension ?: null,
            'size_bytes' => $sizeBytes,
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $user->id,
        ]);
    }

    /**
     * Read upload size before the temp Livewire file is moved/removed by storeAs().
     */
    protected function safeUploadSize(TemporaryUploadedFile|UploadedFile $file): int
    {
        try {
            $size = $file->getSize();

            return is_numeric($size) ? (int) $size : 0;
        } catch (Throwable) {
            // Livewire temp metadata can vanish; fall back after store.
        }

        if ($file instanceof TemporaryUploadedFile) {
            try {
                $real = $file->getRealPath();
                if (is_string($real) && is_file($real)) {
                    return (int) filesize($real);
                }
            } catch (Throwable) {
                // ignore
            }
        }

        return 0;
    }

    public function renameAsset(MediaAsset $asset, string $name): MediaAsset
    {
        $name = trim($name);

        if ($name === '') {
            throw ValidationException::withMessages(['editName' => 'أدخل اسم الملف.']);
        }

        $asset->update(['name' => $name]);

        return $asset->fresh();
    }

    public function saveAltText(MediaAsset $asset, ?string $alt): MediaAsset
    {
        $asset->update(['alt_text' => filled($alt) ? trim($alt) : null]);

        return $asset->fresh();
    }

    public function enablePublicLink(MediaAsset $asset): MediaAsset
    {
        $asset->update([
            'public_enabled' => true,
            'public_token' => $asset->public_token ?: Str::random(40),
        ]);

        return $asset->fresh();
    }

    public function revokePublicLink(MediaAsset $asset): MediaAsset
    {
        $asset->update([
            'public_enabled' => false,
            'public_token' => null,
        ]);

        return $asset->fresh();
    }

    public function deleteAsset(MediaAsset $asset): void
    {
        if (Storage::disk($asset->disk)->exists($asset->path)) {
            Storage::disk($asset->disk)->delete($asset->path);
        }

        $asset->delete();
    }

    public function moveAsset(MediaAsset $asset, ?int $folderId): MediaAsset
    {
        if ($folderId) {
            MediaFolder::query()->findOrFail($folderId);
        }

        if ($asset->folder_id === $folderId) {
            return $asset;
        }

        $disk = Storage::disk($asset->disk);
        $filename = basename($asset->path);
        $dir = self::ROOT.($folderId ? '/'.$folderId : '/root');
        $newPath = $dir.'/'.$filename;

        if ($disk->exists($newPath)) {
            $base = pathinfo($filename, PATHINFO_FILENAME);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $newPath = $dir.'/'.$base.'-'.Str::lower(Str::random(4)).($ext ? '.'.$ext : '');
        }

        if ($disk->exists($asset->path)) {
            $disk->move($asset->path, $newPath);
        }

        $asset->update([
            'folder_id' => $folderId,
            'path' => $newPath,
        ]);

        return $asset->fresh();
    }

    /** @param  array<int, int>  $ids */
    public function deleteMany(array $ids): int
    {
        $count = 0;
        foreach (MediaAsset::query()->whereIn('id', $ids)->get() as $asset) {
            $this->deleteAsset($asset);
            $count++;
        }

        return $count;
    }

    /**
     * @param  array{max_width?:int|null,max_height?:int|null,quality?:int,format?:string,scale?:int}  $options
     */
    public function optimize(MediaAsset $asset, array $options): MediaAsset
    {
        if (! $asset->isImage()) {
            throw ValidationException::withMessages(['optimize' => 'التحسين متاح للصور فقط.']);
        }

        if (! extension_loaded('gd')) {
            throw ValidationException::withMessages(['optimize' => 'امتداد GD غير مفعّل على الخادم.']);
        }

        $source = Storage::disk($asset->disk)->path($asset->path);

        if (! is_file($source)) {
            throw ValidationException::withMessages(['optimize' => 'ملف الصورة غير موجود.']);
        }

        try {
            $result = $this->processImage($source, $options);
        } catch (Throwable $e) {
            throw ValidationException::withMessages(['optimize' => 'تعذّر تحسين الصورة: '.$e->getMessage()]);
        }

        $newExt = $result['extension'];
        $dir = dirname($asset->path);
        $base = pathinfo($asset->path, PATHINFO_FILENAME);
        $newPath = $dir.'/'.$base.'-opt-'.Str::lower(Str::random(6)).'.'.$newExt;

        Storage::disk($asset->disk)->put($newPath, $result['contents']);

        if ($newPath !== $asset->path && Storage::disk($asset->disk)->exists($asset->path)) {
            Storage::disk($asset->disk)->delete($asset->path);
        }

        $displayName = pathinfo($asset->name, PATHINFO_FILENAME).'.'.$newExt;

        $asset->update([
            'path' => $newPath,
            'name' => $displayName,
            'extension' => $newExt,
            'mime_type' => $result['mime'],
            'size_bytes' => strlen($result['contents']),
            'width' => $result['width'],
            'height' => $result['height'],
        ]);

        return $asset->fresh();
    }

    /**
     * @param  array{max_width?:int|null,max_height?:int|null,quality?:int,format?:string,scale?:int}  $options
     * @return array{contents:string,extension:string,mime:string,width:int,height:int}
     */
    protected function processImage(string $sourcePath, array $options): array
    {
        $info = @getimagesize($sourcePath);

        if ($info === false) {
            throw new RuntimeException('صيغة الصورة غير مدعومة.');
        }

        [$srcW, $srcH, $type] = [(int) $info[0], (int) $info[1], (int) $info[2]];
        $source = $this->createImageResource($sourcePath, $type);

        $scale = max(1, min(100, (int) ($options['scale'] ?? 100))) / 100;
        $targetW = (int) max(1, round($srcW * $scale));
        $targetH = (int) max(1, round($srcH * $scale));

        $maxW = isset($options['max_width']) && (int) $options['max_width'] > 0 ? (int) $options['max_width'] : null;
        $maxH = isset($options['max_height']) && (int) $options['max_height'] > 0 ? (int) $options['max_height'] : null;

        if ($maxW || $maxH) {
            $ratio = min(
                $maxW ? $maxW / $targetW : 1,
                $maxH ? $maxH / $targetH : 1,
                1,
            );
            $targetW = (int) max(1, round($targetW * $ratio));
            $targetH = (int) max(1, round($targetH * $ratio));
        }

        $canvas = imagecreatetruecolor($targetW, $targetH);

        if ($canvas === false) {
            imagedestroy($source);
            throw new RuntimeException('تعذّر إنشاء لوحة الصورة.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetW, $targetH, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
        imagedestroy($source);

        $quality = max(50, min(100, (int) ($options['quality'] ?? 85)));
        $format = strtolower((string) ($options['format'] ?? 'keep'));

        if ($format === 'keep') {
            $format = match ($type) {
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
                IMAGETYPE_GIF => 'gif',
                default => 'jpg',
            };
        }

        if ($format === 'webp' && ! function_exists('imagewebp')) {
            imagedestroy($canvas);
            throw new RuntimeException('صيغة WebP غير مدعومة على هذا الخادم.');
        }

        ob_start();
        $ok = match ($format) {
            'png' => imagepng($canvas, null, (int) round((100 - $quality) / 10)),
            'webp' => imagewebp($canvas, null, $quality),
            'gif' => imagegif($canvas),
            default => imagejpeg($canvas, null, $quality),
        };
        $contents = (string) ob_get_clean();
        imagedestroy($canvas);

        if (! $ok || $contents === '') {
            throw new RuntimeException('تعذّر حفظ الصورة المحسّنة.');
        }

        $extension = match ($format) {
            'png' => 'png',
            'webp' => 'webp',
            'gif' => 'gif',
            default => 'jpg',
        };

        $mime = match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return [
            'contents' => $contents,
            'extension' => $extension,
            'mime' => $mime,
            'width' => $targetW,
            'height' => $targetH,
        ];
    }

    /** @return \GdImage */
    protected function createImageResource(string $path, int $type)
    {
        $resource = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if ($resource === false) {
            throw new RuntimeException('تعذّر قراءة ملف الصورة.');
        }

        return $resource;
    }

    protected function uniqueFolderSlug(string $name, ?int $parentId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'folder';
        $slug = $base;
        $i = 2;

        while (
            MediaFolder::query()
                ->where('parent_id', $parentId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
