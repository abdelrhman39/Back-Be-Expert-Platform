<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleMediaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return $this->store($request);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()?->canAdmin('articles.manage'), 403);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:8192'],
        ]);

        $path = $validated['file']->store('articles/content', 'public');

        return response()->json([
            'location' => asset('storage/'.$path),
        ]);
    }
}
