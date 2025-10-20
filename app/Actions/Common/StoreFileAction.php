<?php

declare(strict_types=1);

namespace App\Actions\Common;

use App\Exceptions\FileStorageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final readonly class StoreFileAction
{
    /**
     * Store a file and return its URL.
     *
     * @throws FileStorageException
     */
    public function handle(UploadedFile $file, string $path, string $name): string
    {
        $extension = $file->extension();
        $filename = sprintf('%s-%s.%s', $name, Str::uuid(), $extension);
        $storedPath = $file->storePubliclyAs($path, $filename);

        if (! $storedPath) {
            throw FileStorageException::failedToStore();
        }

        return Storage::url($storedPath);
    }
}
