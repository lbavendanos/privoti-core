<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class CannotDeleteDefaultAddressException extends DomainException
{
    public static function create(): self
    {
        return new self(
            'Cannot delete the default address. Please set another address as default before deleting this one.'
        );
    }
}
