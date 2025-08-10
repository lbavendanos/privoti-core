<?php

namespace App\Models;

use App\Traits\TimestampsScope;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Propaganistas\LaravelPhone\PhoneNumber;

class Customer extends Authenticatable implements MustVerifyEmail
{
    use TimestampsScope, HasFactory, Notifiable, SoftDeletes;

    const ACCOUNT_LIST = ['guest', 'registered'];
    const ACCOUNT_DEFAULT = 'guest';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'dob',
        'account',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Interact with the user's first name.
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucwords($value),
            set: fn(string $value) => strtolower($value),
        );
    }

    /**
     * Interact with the user's last name.
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => ucwords($value),
            set: fn(string $value) => strtolower($value),
        );
    }

    /**
     * Get the customer's name attribute with proper formatting.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Get the customer's phone.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if (blank($value)) return null;

                $countryCode = config('core.country_code');
                $phoneNumber = new PhoneNumber($value, $countryCode);

                return [
                    'e164' => $phoneNumber->formatE164(),
                    'international' => $phoneNumber->formatInternational(),
                    'national' => $phoneNumber->formatNational(),
                    'mobile_dialing' => $phoneNumber->formatForMobileDialingInCountry($countryCode),
                ];
            },
            set: fn(mixed $value) => filled($value) ?
                (new PhoneNumber($value, config('core.country_code')))->formatE164()
                : null
        );
    }

    /**
     * Get the user's addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
