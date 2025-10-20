<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class FileStorageException extends RuntimeException
{
    public static function failedToStore(): self
    {
        return new self('Failed to store file.');
    }
}
