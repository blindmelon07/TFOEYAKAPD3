<?php

namespace App\Models\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

trait ResolvesAssetUrls
{
    /**
     * Resolve a stored asset path to a browser-usable URL.
     *
     * Absolute URLs and root-relative paths (e.g. "/images/logo.png") are
     * returned untouched; anything else is treated as a file on the public disk.
     */
    public static function assetUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Store an uploaded image on the public disk and return its path.
     *
     * @throws ValidationException
     */
    public static function storeUploadedAsset(UploadedFile $file, string $directory, string $field): string
    {
        $path = $file->store($directory, 'public');

        if ($path === false) {
            throw ValidationException::withMessages([
                $field => 'The image could not be saved. Please try again.',
            ]);
        }

        return $path;
    }

    /**
     * Delete a previously uploaded asset from the public disk.
     */
    public static function deleteUploadedAsset(?string $path): void
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
