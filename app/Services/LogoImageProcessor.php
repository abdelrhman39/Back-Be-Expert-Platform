<?php

namespace App\Services;

use App\Support\LogoSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

class LogoImageProcessor
{
    /**
     * @param  TemporaryUploadedFile|UploadedFile  $file
     */
    public function storeOptimized(TemporaryUploadedFile|UploadedFile $file, string $settingKey): string
    {
        if (! extension_loaded('gd')) {
            return $this->storeRaw($file);
        }

        try {
            $slot = LogoSettings::slot($settingKey);
            $optimized = $this->optimize($file->getRealPath(), $slot);
            $filename = 'platform-logos/'.Str::uuid().'.'.$optimized['extension'];
            Storage::disk('public')->put($filename, $optimized['contents']);

            return '/storage/'.$filename;
        } catch (Throwable) {
            return $this->storeRaw($file);
        }
    }

    /**
     * @param  array{max_width: int, max_height: int, square?: bool}  $slot
     * @return array{contents: string, extension: string}
     */
    public function optimize(string $sourcePath, array $slot): array
    {
        [$width, $height, $type] = $this->readImageInfo($sourcePath);
        $drawWidth = $this->scaledWidth($width, $height, $slot);
        $drawHeight = $this->scaledHeight($width, $height, $slot);

        if ($slot['square'] ?? false) {
            $canvasWidth = min($slot['max_width'], $slot['max_height']);
            $canvasHeight = $canvasWidth;
            $offsetX = (int) floor(($canvasWidth - $drawWidth) / 2);
            $offsetY = (int) floor(($canvasHeight - $drawHeight) / 2);
        } else {
            $canvasWidth = $drawWidth;
            $canvasHeight = $drawHeight;
            $offsetX = 0;
            $offsetY = 0;
        }

        $source = $this->createImageResource($sourcePath, $type);
        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        if ($canvas === false) {
            imagedestroy($source);
            throw new RuntimeException('Unable to allocate image canvas.');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $canvasWidth, $canvasHeight, $transparent);

        imagecopyresampled(
            $canvas,
            $source,
            $offsetX,
            $offsetY,
            0,
            0,
            $drawWidth,
            $drawHeight,
            $width,
            $height,
        );

        imagedestroy($source);

        ob_start();
        imagepng($canvas, null, 6);
        $contents = (string) ob_get_clean();
        imagedestroy($canvas);

        return ['contents' => $contents, 'extension' => 'png'];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected function readImageInfo(string $path): array
    {
        $info = @getimagesize($path);

        if ($info === false) {
            throw new RuntimeException('Unsupported image file.');
        }

        return [(int) $info[0], (int) $info[1], (int) $info[2]];
    }

    /**
     * @param  array{max_width: int, max_height: int, square?: bool}  $slot
     */
    protected function scaledWidth(int $width, int $height, array $slot): int
    {
        $scale = min(
            $slot['max_width'] / max($width, 1),
            $slot['max_height'] / max($height, 1),
            1,
        );

        return max(1, (int) round($width * $scale));
    }

    /**
     * @param  array{max_width: int, max_height: int, square?: bool}  $slot
     */
    protected function scaledHeight(int $width, int $height, array $slot): int
    {
        $scale = min(
            $slot['max_width'] / max($width, 1),
            $slot['max_height'] / max($height, 1),
            1,
        );

        return max(1, (int) round($height * $scale));
    }

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
            throw new RuntimeException('Unable to read image contents.');
        }

        return $resource;
    }

    /**
     * @param  TemporaryUploadedFile|UploadedFile  $file
     */
    protected function storeRaw(TemporaryUploadedFile|UploadedFile $file): string
    {
        $stored = $file->store('platform-logos', 'public');

        return '/storage/'.$stored;
    }
}
