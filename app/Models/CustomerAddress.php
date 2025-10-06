<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Common\FormatPhoneNumberAction;
use App\Actions\Common\NormalizePhoneNumberAction;
use Database\Factories\CustomerAddressFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CustomerAddress extends Model
{
    /** @use HasFactory<CustomerAddressFactory> */
    use HasFactory;

    use SoftDeletes;

    public const int MAX_ADDRESSES_PER_CUSTOMER = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'address1',
        'address2',
        'district',
        'city',
        'state',
        'default',
    ];

    /**
     * Get the customer that owns the address.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default' => 'boolean',
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
     * Get and set the phone number attribute.
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
