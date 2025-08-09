<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Propaganistas\LaravelPhone\PhoneNumber;

class Customer extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\CustomerrFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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

                $phoneNumber = new PhoneNumber($value, config('app.country_code'));

                return [
                    'e164' => $phoneNumber->formatE164(),
                    'international' => $phoneNumber->formatInternational(),
                    'national' => $phoneNumber->formatNational(),
                ];
            },
            set: fn(mixed $value) => filled($value) ?
                (new PhoneNumber($value, config('app.country_code')))->formatE164()
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

    /**
     * Scope a query to only include products created between the given dates.
     */
    #[Scope]
    protected function createdBetween(Builder $query, array $dates)
    {
        $timezone = config('app.timezone');
        [$start, $end] = array_map(fn($date) => Carbon::parse($date)->setTimezone($timezone), $dates);

        $query->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }

    /**
     * Scope a query to only include products created on a specific date.
     */
    #[Scope]
    protected function createdAt(Builder $query, $date)
    {
        $timezone = config('app.timezone');
        $date = Carbon::parse($date)->setTimezone($timezone);

        $query->whereDate('created_at', $date);
    }

    /**
     * Scope a query to only include products updated between the given dates.
     */
    #[Scope]
    protected function updatedBetween(Builder $query, array $dates)
    {
        $timezone = config('app.timezone');
        [$start, $end] = array_map(fn($date) => Carbon::parse($date)->setTimezone($timezone), $dates);

        $query->whereDate('updated_at', '>=', $start)
            ->whereDate('updated_at', '<=', $end);
    }

    /**
     * Scope a query to only include products updated on a specific date.
     */
    #[Scope]
    protected function updatedAt(Builder $query, $date)
    {
        $timezone = config('app.timezone');
        $date = Carbon::parse($date)->setTimezone($timezone);

        $query->whereDate('updated_at', $date);
    }
}
