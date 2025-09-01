<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Common\FormatPhoneNumberAction;
use App\Actions\Common\NormalizePhoneNumberAction;
use App\Traits\TimestampsScope;
use Database\Factories\CustomerFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class Customer extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use Notifiable;
    use SoftDeletes;
    use TimestampsScope;

    public const array ACCOUNT_LIST = ['guest', 'registered'];

    public const string ACCOUNT_DEFAULT = 'guest';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the customer's addresses.
     *
     * @return HasMany<CustomerAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

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
     *
     * @return Attribute<string, string>
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): string => is_string($value) ? ucwords($value) : '',
            set: fn (string $value) => mb_strtolower($value),
        );
    }

    /**
     * Interact with the user's last name.
     *
     * @return Attribute<string, string>
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): string => is_string($value) ? ucwords($value) : '',
            set: fn (string $value) => mb_strtolower($value),
        );
    }

    /**
     * Get the customer's name attribute with proper formatting.
     *
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => sprintf('%s %s', $this->first_name, $this->last_name),
        );
    }

    /**
     * Get the customer's phone.
     *
     * @return Attribute<array<string, string>|null, string|null>
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): ?array => app(FormatPhoneNumberAction::class)->handle(is_string($value) ? $value : null),
            set: fn (?string $value): ?string => app(NormalizePhoneNumberAction::class)->handle($value)
        );
    }
}
