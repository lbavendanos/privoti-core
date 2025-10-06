<?php

declare(strict_types=1);

use App\Actions\CustomerAddress\UpdateCustomerAddressAction;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('updates a customer address by model instance', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '912345678',
        'address1' => 'Urb. Valery  Crespo # 46 Hab. 463',
        'address2' => 'Hab. 834',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    $updated = (new UpdateCustomerAddressAction())->handle($customer, $address, $attributes);

    expect($updated)->toBeInstanceOf(CustomerAddress::class)
        ->and($updated->first_name)->toBe($attributes['first_name'])
        ->and($updated->last_name)->toBe($attributes['last_name'])
        ->and($updated->phone)->toBe([
            'e164' => '+51912345678',
            'international' => '+51 912 345 678',
            'national' => '912 345 678',
            'mobile_dialing' => '912345678',
        ])
        ->and($updated->address1)->toBe($attributes['address1'])
        ->and($updated->address2)->toBe($attributes['address2'])
        ->and($updated->district)->toBe($attributes['district'])
        ->and($updated->city)->toBe($attributes['city'])
        ->and($updated->state)->toBe($attributes['state']);
});

it('updates a customer address by id', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '912345678',
        'address1' => 'Urb. Valery  Crespo # 46 Hab. 463',
        'address2' => 'Hab. 834',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    $updated = (new UpdateCustomerAddressAction())->handle($customer->id, $address->id, $attributes);

    expect($updated)->toBeInstanceOf(CustomerAddress::class)
        ->and($updated->first_name)->toBe($attributes['first_name'])
        ->and($updated->last_name)->toBe($attributes['last_name'])
        ->and($updated->phone)->toBe([
            'e164' => '+51912345678',
            'international' => '+51 912 345 678',
            'national' => '912 345 678',
            'mobile_dialing' => '912345678',
        ])
        ->and($updated->address1)->toBe($attributes['address1'])
        ->and($updated->address2)->toBe($attributes['address2'])
        ->and($updated->district)->toBe($attributes['district'])
        ->and($updated->city)->toBe($attributes['city'])
        ->and($updated->state)->toBe($attributes['state']);
});

it('throws an exception when the customer does not exist', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '912345678',
        'address1' => 'Urb. Valery  Crespo # 46 Hab. 463',
        'address2' => 'Hab. 834',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    (new UpdateCustomerAddressAction())->handle(999999, $address, $attributes);
})->throws(ModelNotFoundException::class);

it('throws an exception when the address does not exist', function () {
    $customer = Customer::factory()->create();
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '912345678',
        'address1' => 'Urb. Valery  Crespo # 46 Hab. 463',
        'address2' => 'Hab. 834',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    (new UpdateCustomerAddressAction())->handle($customer, 999999, $attributes);
})->throws(ModelNotFoundException::class);

it('throws an exception when neither the customer nor the address exist', function () {
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '912345678',
        'address1' => 'Urb. Valery  Crespo # 46 Hab. 463',
        'address2' => 'Hab. 834',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    (new UpdateCustomerAddressAction())->handle(999999, 999999, $attributes);
})->throws(ModelNotFoundException::class);

it('throws an exception when the address does not belong to the given customer', function () {
    $customer = Customer::factory()->create();
    $otherCustomer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($otherCustomer)->create();
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '912345678',
        'address1' => 'Urb. Valery  Crespo # 46 Hab. 463',
        'address2' => 'Hab. 834',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    (new UpdateCustomerAddressAction())->handle($customer, $address, $attributes);
})->throws(ModelNotFoundException::class);

it('sets the address as default when the default attribute is true', function () {
    $customer = Customer::factory()->create();
    $address1 = CustomerAddress::factory()->for($customer)->create(['default' => true]);
    $address2 = CustomerAddress::factory()->for($customer)->create(['default' => false]);
    $attributes = ['default' => true];

    $updated = (new UpdateCustomerAddressAction())->handle($customer, $address2, $attributes);

    $address1->refresh();

    expect($updated)->toBeInstanceOf(CustomerAddress::class)
        ->and($updated->id)->toBe($address2->id)
        ->and($updated->default)->toBeTrue()
        ->and($address1->default)->toBeFalse();
});

it('keeps the address as default when the default attribute is false but there is no other default address', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create(['default' => true]);
    $attributes = ['default' => false];

    $updated = (new UpdateCustomerAddressAction())->handle($customer, $address, $attributes);

    expect($updated)->toBeInstanceOf(CustomerAddress::class)
        ->and($updated->id)->toBe($address->id)
        ->and($updated->default)->toBeTrue();
});

it('updates the address without changing the default status when the default attribute is not provided', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create(['default' => true]);
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '912345678',
        'address1' => 'Urb. Valery  Crespo # 46 Hab. 463',
        'address2' => 'Hab. 834',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    $updated = (new UpdateCustomerAddressAction())->handle($customer, $address, $attributes);

    expect($updated)->toBeInstanceOf(CustomerAddress::class)
        ->and($updated->id)->toBe($address->id)
        ->and($updated->default)->toBeTrue();
});
