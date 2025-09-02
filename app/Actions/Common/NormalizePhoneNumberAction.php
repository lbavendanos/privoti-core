<?php

declare(strict_types=1);

namespace App\Actions\Common;

use Illuminate\Support\Facades\Config;
use Propaganistas\LaravelPhone\PhoneNumber;

final readonly class NormalizePhoneNumberAction
{
    /**
     * Normalize a phone number to E.164 format.
     */
    public function handle(?string $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        return new PhoneNumber($value, Config::string('core.country_code'))->formatE164();
    }
}
