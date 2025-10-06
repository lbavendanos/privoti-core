<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

final class MaxAddressesLimitExceededException extends DomainException
{
    public static function forCustomer(int $maxAddresses): self
    {
        return new self(
            "A customer cannot have more than {$maxAddresses} addresses."
        );
    }
}
