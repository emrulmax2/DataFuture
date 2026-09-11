<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Serves a book cover by name, for the Operations library sync.
 *
 * These files are also reachable under /storage/amazon_book, but 152 of them
 * have a literal "%" in the filename — left over from Amazon URLs — and the
 * web server refuses to serve a path containing one however it is encoded.
 * Taking the name as a query parameter avoids the path entirely.
 */
class LibraryCoverController extends Controller
{
    private const DIR = 'public/amazon_book/';

    public function show(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        // The name is a single filename, never a path. Anything that could walk
        // out of the directory is refused rather than sanitised.
        $name = $data['name'];

        if (str_contains($name, '/') || str_contains($name, '\\') || str_contains($name, '..')) {
            return response()->json(['message' => 'Invalid name.'], 422);
        }

        $relative = self::DIR . $name;

        if (! Storage::disk('local')->exists($relative)) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->streamDownload(
            fn () => print(Storage::disk('local')->get($relative)),
            $name,
            ['Content-Type' => Storage::disk('local')->mimeType($relative) ?: 'application/octet-stream']
        );
    }
}
