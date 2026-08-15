<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Shared handling for the "upload a file, or paste a URL" image fields used by
 * article thumbnails and author avatars.
 *
 * Uploads are saved to the public disk and stored in the database as a
 * root-relative "/storage/..." path, which keeps the value portable if the
 * domain changes. Anything else is treated as an external URL we do not own.
 */
trait ManagesImageField
{
    /**
     * Work out the new value for an image column, and clean up the old upload.
     *
     * Precedence: a newly uploaded file wins; then the "remove" checkbox;
     * otherwise the URL text field is the source of truth.
     *
     * @param  string  $fileKey  name of the file input (e.g. "thumbnail")
     * @param  string  $urlKey  name of the URL text input (e.g. "thumbnail_url")
     * @param  string|null  $current  the value currently stored on the model
     * @param  string  $directory  folder on the public disk
     */
    protected function resolveImageField(
        Request $request,
        string $fileKey,
        string $urlKey,
        ?string $current,
        string $directory,
    ): ?string {
        $next = null;

        if ($request->hasFile($fileKey)) {
            $path = $request->file($fileKey)->store($directory, 'public');
            $next = '/storage/'.$path;
        } elseif (! $request->boolean('remove_'.$fileKey)) {
            $url = trim((string) $request->input($urlKey, ''));
            $next = $url !== '' ? $url : null;
        }

        // The previous file is now unreferenced — delete it so uploads do not
        // pile up on disk. External URLs are left alone.
        if ($current !== $next) {
            $this->deleteManagedImage($current);
        }

        return $next;
    }

    /**
     * Delete an uploaded image, ignoring anything hosted elsewhere.
     */
    protected function deleteManagedImage(?string $value): void
    {
        if (! $value || ! str_starts_with($value, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(substr($value, strlen('/storage/')));
    }
}
