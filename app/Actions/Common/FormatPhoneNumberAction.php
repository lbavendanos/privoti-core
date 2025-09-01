<?php

declare(strict_types=1);

namespace App\Actions\Common;

use Illuminate\Support\Facades\Config;
use Propaganistas\LaravelPhone\PhoneNumber;

final readonly class FormatPhoneNumberAction
{
    /**
     * Format a phone number into various formats.
     *
     * @return array{e164: string, international: string, national: string, mobile_dialing: string}|null
     */
    public function handle(?string $value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        $countryCode = Config::string('core.country_code');
        $phoneNumber = new PhoneNumber($value, $countryCode);

        return [
            'e164' => $phoneNumber->formatE164(),
            'international' => $phoneNumber->formatInternational(),
            'national' => $phoneNumber->formatNational(),
            'mobile_dialing' => $phoneNumber->formatForMobileDialingInCountry($countryCode),
        ];
    }
}
