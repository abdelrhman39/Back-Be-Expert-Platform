<?php

namespace App\Http\Controllers\Media;

use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaPublicController
{
    public function __invoke(Request $request, string $token): StreamedResponse
    {
        $asset = MediaAsset::query()
            ->where('public_token', $token)
            ->where('public_enabled', true)
            ->firstOrFail();

        $disk = Storage::disk($asset->disk);

        abort_unless($disk->exists($asset->path), 404);

        return $disk->response(
            $asset->path,
            $asset->name,
            [
                'Content-Type' => $asset->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=86400',
            ],
        );
    }
}
