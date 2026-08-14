<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\SessionMaterial;
use App\Models\User;
use App\Support\SessionMaterialOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SessionMaterialService
{
    public function uploadFile(
        AttendanceSession $session,
        User $user,
        UploadedFile $file,
        string $title,
        string $visibility = 'published',
    ): SessionMaterial {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, SessionMaterialOptions::allowedExtensions(), true)) {
            throw ValidationException::withMessages([
                'materialFile' => 'نوع الملف غير مسموح.',
            ]);
        }

        if ($file->getSize() > SessionMaterialOptions::maxFileKb() * 1024) {
            throw ValidationException::withMessages([
                'materialFile' => 'حجم الملف يتجاوز الحد المسموح.',
            ]);
        }

        $path = $file->store(
            "sessions/{$session->section_id}/{$session->id}",
            'public'
        );

        return SessionMaterial::query()->create([
            'attendance_session_id' => $session->id,
            'type' => 'file',
            'title' => $title,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'visibility' => $visibility,
            'uploaded_by' => $user->id,
            'published_at' => $visibility === 'published' ? now() : null,
            'sort_order' => (int) SessionMaterial::query()->where('attendance_session_id', $session->id)->max('sort_order') + 1,
        ]);
    }

    public function addLink(
        AttendanceSession $session,
        User $user,
        string $title,
        string $url,
        string $visibility = 'published',
    ): SessionMaterial {
        return SessionMaterial::query()->create([
            'attendance_session_id' => $session->id,
            'type' => 'link',
            'title' => $title,
            'external_url' => $url,
            'visibility' => $visibility,
            'uploaded_by' => $user->id,
            'published_at' => $visibility === 'published' ? now() : null,
            'sort_order' => (int) SessionMaterial::query()->where('attendance_session_id', $session->id)->max('sort_order') + 1,
        ]);
    }

    public function delete(SessionMaterial $material): void
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();
    }
}
