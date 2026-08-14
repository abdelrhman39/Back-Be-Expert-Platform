<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class SiteFileManagerService
{
    protected array $blockedNames = [
        '.',
        '..',
    ];

    public function root(): string
    {
        return rtrim(str_replace('\\', '/', base_path()), '/');
    }

    public function resolve(?string $relative): string
    {
        $root = $this->root();
        $relative = trim(str_replace('\\', '/', (string) $relative), '/');

        if ($relative === '' || $relative === '.') {
            return $root;
        }

        if (str_contains($relative, "\0") || preg_match('#(^|/)\.\.(/|$)#', $relative)) {
            throw ValidationException::withMessages(['path' => 'مسار غير صالح.']);
        }

        $absolute = $root.'/'.$relative;
        $realRoot = realpath($root) ?: $root;
        $parent = dirname($absolute);
        $realParent = is_dir($parent) ? (realpath($parent) ?: $parent) : $parent;

        $normalizedAbsolute = str_replace('\\', '/', $absolute);
        $normalizedRoot = str_replace('\\', '/', $realRoot);
        $normalizedParent = str_replace('\\', '/', $realParent);

        if (
            $normalizedAbsolute !== $normalizedRoot
            && ! str_starts_with($normalizedAbsolute, $normalizedRoot.'/')
            && ! str_starts_with($normalizedParent, $normalizedRoot)
        ) {
            throw ValidationException::withMessages(['path' => 'غير مسموح بالخروج من جذر الموقع.']);
        }

        return $absolute;
    }

    public function relativeOf(string $absolute): string
    {
        $root = $this->root();
        $absolute = str_replace('\\', '/', $absolute);
        $root = str_replace('\\', '/', $root);

        if ($absolute === $root) {
            return '';
        }

        if (str_starts_with($absolute, $root.'/')) {
            return substr($absolute, strlen($root) + 1);
        }

        throw ValidationException::withMessages(['path' => 'مسار خارج الجذر.']);
    }

    public function list(?string $relative, string $search = ''): array
    {
        $dir = $this->resolve($relative);

        if (! is_dir($dir)) {
            throw ValidationException::withMessages(['path' => 'المجلد غير موجود.']);
        }

        $items = [];
        $entries = @scandir($dir) ?: [];

        foreach ($entries as $name) {
            if (in_array($name, $this->blockedNames, true)) {
                continue;
            }

            if ($search !== '' && ! str_contains(mb_strtolower($name), mb_strtolower($search))) {
                continue;
            }

            $full = $dir.DIRECTORY_SEPARATOR.$name;
            $isDir = is_dir($full);
            $size = $isDir ? 0 : (int) (@filesize($full) ?: 0);
            $ext = $isDir ? null : strtolower(pathinfo($name, PATHINFO_EXTENSION) ?: '');

            $items[] = [
                'name' => $name,
                'path' => ltrim($this->relativeOf(str_replace('\\', '/', $full)), '/'),
                'type' => $isDir ? 'dir' : 'file',
                'size' => $size,
                'mtime' => (int) (@filemtime($full) ?: 0),
                'ext' => $ext !== '' ? $ext : null,
                'editable' => ! $isDir && $this->isEditable($full, $ext ?: null),
            ];
        }

        usort($items, function (array $a, array $b): int {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'dir' ? -1 : 1;
            }

            return strnatcasecmp($a['name'], $b['name']);
        });

        return $items;
    }

    public function breadcrumbs(?string $relative): array
    {
        $relative = trim(str_replace('\\', '/', (string) $relative), '/');
        $crumbs = [['label' => '/', 'path' => '']];

        if ($relative === '') {
            return $crumbs;
        }

        $parts = explode('/', $relative);
        $acc = [];
        foreach ($parts as $part) {
            $acc[] = $part;
            $crumbs[] = [
                'label' => $part,
                'path' => implode('/', $acc),
            ];
        }

        return $crumbs;
    }

    public function createDirectory(?string $relative, string $name): string
    {
        $name = trim($name);
        $this->assertSafeName($name);
        $parent = $this->resolve($relative);
        $target = $parent.DIRECTORY_SEPARATOR.$name;

        if (file_exists($target)) {
            throw ValidationException::withMessages(['name' => 'يوجد ملف أو مجلد بنفس الاسم.']);
        }

        if (! @mkdir($target, 0755, false)) {
            throw ValidationException::withMessages(['name' => 'تعذّر إنشاء المجلد.']);
        }

        return $this->relativeOf(str_replace('\\', '/', $target));
    }

    public function upload(?string $relative, TemporaryUploadedFile|UploadedFile $file): string
    {
        $parent = $this->resolve($relative);
        $original = $file->getClientOriginalName();
        $this->assertSafeName($original);
        $target = $parent.DIRECTORY_SEPARATOR.$original;

        if (file_exists($target)) {
            $base = pathinfo($original, PATHINFO_FILENAME);
            $ext = pathinfo($original, PATHINFO_EXTENSION);
            $target = $parent.DIRECTORY_SEPARATOR.$base.'-'.bin2hex(random_bytes(3)).($ext ? '.'.$ext : '');
        }

        $source = $file->getRealPath();
        if (! is_string($source) || $source === '' || ! is_file($source)) {
            throw ValidationException::withMessages(['uploads' => 'تعذّر قراءة الملف المرفوع.']);
        }

        if (! @copy($source, $target)) {
            try {
                $file->move(dirname($target), basename($target));
            } catch (Throwable) {
                throw ValidationException::withMessages(['uploads' => 'تعذّر حفظ الملف على السيرفر.']);
            }
        }

        return $this->relativeOf(str_replace('\\', '/', $target));
    }

    public function rename(string $relative, string $newName): string
    {
        $newName = trim($newName);
        $this->assertSafeName($newName);
        $source = $this->resolve($relative);

        if (! file_exists($source)) {
            throw ValidationException::withMessages(['name' => 'العنصر غير موجود.']);
        }

        $target = dirname($source).DIRECTORY_SEPARATOR.$newName;

        if (file_exists($target)) {
            throw ValidationException::withMessages(['name' => 'يوجد عنصر بنفس الاسم الجديد.']);
        }

        if (! @rename($source, $target)) {
            throw ValidationException::withMessages(['name' => 'تعذّر إعادة التسمية.']);
        }

        return $this->relativeOf(str_replace('\\', '/', $target));
    }

    public function delete(string $relative): void
    {
        $relative = trim(str_replace('\\', '/', $relative), '/');

        if ($relative === '') {
            throw ValidationException::withMessages(['path' => 'لا يمكن حذف جذر الموقع.']);
        }

        $target = $this->resolve($relative);

        if (! file_exists($target)) {
            throw ValidationException::withMessages(['path' => 'العنصر غير موجود.']);
        }

        if (is_dir($target)) {
            if (! File::deleteDirectory($target)) {
                throw ValidationException::withMessages(['path' => 'تعذّر حذف المجلد.']);
            }

            return;
        }

        if (! @unlink($target)) {
            throw ValidationException::withMessages(['path' => 'تعذّر حذف الملف.']);
        }
    }

    public function read(string $relative): string
    {
        $path = $this->resolve($relative);

        if (! is_file($path)) {
            throw ValidationException::withMessages(['path' => 'الملف غير موجود.']);
        }

        if (! $this->isEditable($path, strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: null))) {
            throw ValidationException::withMessages(['path' => 'هذا الملف غير قابل للتحرير كنص.']);
        }

        $size = (int) (@filesize($path) ?: 0);
        if ($size > 2 * 1024 * 1024) {
            throw ValidationException::withMessages(['path' => 'الملف أكبر من 2 م.ب للتحرير.']);
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw ValidationException::withMessages(['path' => 'تعذّر قراءة الملف.']);
        }

        return $contents;
    }

    public function write(string $relative, string $contents): void
    {
        $path = $this->resolve($relative);

        if (! is_file($path)) {
            throw ValidationException::withMessages(['path' => 'الملف غير موجود.']);
        }

        if (! $this->isEditable($path, strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: null))) {
            throw ValidationException::withMessages(['path' => 'هذا الملف غير قابل للتحرير كنص.']);
        }

        if (@file_put_contents($path, $contents) === false) {
            throw ValidationException::withMessages(['path' => 'تعذّر حفظ الملف.']);
        }
    }

    public function createFile(?string $relative, string $name, string $contents = ''): string
    {
        $name = trim($name);
        $this->assertSafeName($name);
        $parent = $this->resolve($relative);
        $target = $parent.DIRECTORY_SEPARATOR.$name;

        if (file_exists($target)) {
            throw ValidationException::withMessages(['name' => 'يوجد ملف بنفس الاسم.']);
        }

        if (@file_put_contents($target, $contents) === false) {
            throw ValidationException::withMessages(['name' => 'تعذّر إنشاء الملف.']);
        }

        return $this->relativeOf(str_replace('\\', '/', $target));
    }

    public function humanBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' بايت';
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1).' ك.ب';
        }

        return number_format($bytes / (1024 * 1024), 1).' م.ب';
    }

    protected function assertSafeName(string $name): void
    {
        if ($name === '' || $name === '.' || $name === '..') {
            throw ValidationException::withMessages(['name' => 'اسم غير صالح.']);
        }

        if (str_contains($name, '/') || str_contains($name, '\\') || str_contains($name, "\0")) {
            throw ValidationException::withMessages(['name' => 'الاسم لا يجب أن يحتوي على مسارات.']);
        }
    }

    protected function isEditable(string $path, ?string $ext): bool
    {
        $ext = strtolower((string) $ext);
        $allowed = [
            'txt', 'md', 'json', 'xml', 'yml', 'yaml', 'csv', 'log', 'env', 'ini', 'conf',
            'php', 'js', 'css', 'scss', 'html', 'htm', 'blade.php', 'vue', 'ts', 'tsx', 'jsx',
            'svg', 'sql', 'htaccess', 'gitignore', 'editorconfig', 'lock',
        ];

        $base = strtolower(basename($path));
        if (in_array($base, ['.env', '.htaccess', '.gitignore', 'robots.txt'], true)) {
            return true;
        }

        if ($ext !== '' && in_array($ext, $allowed, true)) {
            return true;
        }

        if (str_ends_with($base, '.blade.php')) {
            return true;
        }

        try {
            $sample = @file_get_contents($path, false, null, 0, 512);
            if ($sample === false) {
                return false;
            }

            return ! preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $sample);
        } catch (Throwable) {
            return false;
        }
    }
}
