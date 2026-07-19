<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Resolves an order item's stored product_image_path into a browser-usable URL.
 *
 * The column holds two shapes, for historical reasons:
 *  - Absolute URLs from the legacy import (e.g. "https://shivdharamarbo.com/...")
 *    — the overwhelming majority of rows. These are already fully qualified and
 *    must be returned untouched; wrapping them in Storage::url() produces a
 *    broken "<app>/storage/https://…" address, which is why images stopped
 *    rendering in the app even though the raw link opens fine in a browser.
 *  - Relative paths from in-app uploads (e.g. "images/photo.png"), which live on
 *    the public disk and need Storage::url() to become absolute.
 */
final class ProductImage
{
    public static function url(?string $path): ?string
    {
        $path = $path !== null ? trim($path) : '';
        if ($path === '') {
            return null;
        }

        // Already absolute (legacy import): hand it back as-is, only encoding
        // literal spaces so it is a valid <img src> (already-encoded %20 is left
        // alone — there are no raw spaces to touch in that case).
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return str_replace(' ', '%20', $path);
        }

        // Relative path on the public disk (in-app upload).
        return Storage::disk('public')->url($path);
    }
}
