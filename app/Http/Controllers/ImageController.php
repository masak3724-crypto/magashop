<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function show(Request $request, string $path)
    {
        $path = ltrim($path, '/');

        // Only allow serving from /public/images to avoid path traversal.
        if (! str_starts_with($path, 'images/') || str_contains($path, '..')) {
            abort(404);
        }

        $fullPath = public_path($path);
        if (! is_file($fullPath)) {
            abort(404);
        }

        $etag = '"'.md5_file($fullPath).'"';
        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';
        $lastModified = gmdate('D, d M Y H:i:s', filemtime($fullPath)).' GMT';

        return Response::file($fullPath, [
            'Content-Type' => $mime,
            // Cache aggressively – assets are versioned by filename in our app.
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'ETag' => $etag,
            'Last-Modified' => $lastModified,
        ]);
    }
}

