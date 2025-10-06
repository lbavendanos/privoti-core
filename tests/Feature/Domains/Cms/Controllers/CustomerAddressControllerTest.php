<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

beforeEach(function () {
    $user = User::factory()->create();
    /** @var TestCase $this */
    $this->actingAs($user, 'cms');
});

it('returns a customer address collection', function () {
    $customer = Customer::factory()->create();
    $addresses = CustomerAddress::factory()->count(10)->for($customer)->create();

    /** @var TestCase $this */
    $response = $this->getJson(sprintf('/api/c/customers/%s/addresses', $customer->id));

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 10)
            ->has('meta')
        );
});

it('creates a customer address', function () {
    $customer = Customer::factory()->create();
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '987654321',
        'address1' => 'Av. Natalia Zayas',
        'address2' => 'Dpto. 101',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    /** @var TestCase $this */
    $response = $this->postJson(sprintf('/api/c/customers/%s/addresses', $customer->id), $attributes);

    $response
        ->assertCreated()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.first_name', $attributes['first_name'])
            ->where('data.last_name', $attributes['last_name'])
            ->where('data.phone', [
                'e164' => '+51987654321',
                'international' => '+51 987 654 321',
                'national' => '987 654 321',
                'mobile_dialing' => '987654321',
            ])
            ->where('data.address1', $attributes['address1'])
            ->where('data.address2', $attributes['address2'])
            ->where('data.district', $attributes['district'])
            ->where('data.city', $attributes['city'])
            ->where('data.state', $attributes['state'])
        );
});

it('throws a validation error when creating a customer address with invalid attributes', function () {
    $customer = Customer::factory()->create();
    $attributes = [
        'first_name' => '',
        'last_name' => '',
        'phone' => 'invalid-phone',
        'address1' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    /** @var TestCase $this */
    $response = $this->postJson(sprintf('/api/c/customers/%s/addresses', $customer->id), $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'phone', 'address1', 'district', 'city', 'state']);
});

it('throws a not found error when creating an address for a non-existing customer', function () {
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '987654321',
        'address1' => 'Av. Natalia Zayas',
        'address2' => 'Dpto. 101',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/api/c/customers/999/addresses', $attributes);

    $response->assertNotFound();
});

it('throws a validation error when creating more than the maximum allowed addresses for a customer', function () {
    $customer = Customer::factory()->create();
    CustomerAddress::factory()->count(CustomerAddress::MAX_ADDRESSES_PER_CUSTOMER)->for($customer)->create();

    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone' => '987654321',
        'address1' => 'Av. Natalia Zayas',
        'address2' => 'Dpto. 101',
        'district' => 'Lima',
        'city' => 'Lima',
        'state' => 'Lima',
    ];

    /** @var TestCase $this */
    $response = $this->postJson(sprintf('/api/c/customers/%s/addresses', $customer->id), $attributes);

    $response->assertForbidden();
});

it('shows a customer address', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    /** @var TestCase $this */
    $response = $this->getJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id));

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $address->id)
            ->where('data.first_name', $address->first_name)
            ->where('data.last_name', $address->last_name)
            ->etc()
        );
});

it('returns 404 when showing a non-existing customer address', function () {
    $customer = Customer::factory()->create();

    /** @var TestCase $this */
    $response = $this->getJson(sprintf('/api/c/customers/%s/addresses/999999', $customer->id));

    $response->assertNotFound();
});

it('returns 404 when showing a customer address that does not belong to the customer', function () {
    $customer = Customer::factory()->create();
    $otherCustomer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($otherCustomer)->create();

    /** @var TestCase $this */
    $response = $this->getJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id));

    $response->assertNotFound();
});

it('returns 404 when showing a customer address for a non-existing customer', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    /** @var TestCase $this */
    $response = $this->getJson(sprintf('/api/c/customers/999/addresses/%s', $address->id));

    $response->assertNotFound();
});

it('returns 404 when showing a non-existing customer address for a non-existing customer', function () {
    /** @var TestCase $this */
    $response = $this->getJson('/api/c/customers/999/addresses/999');

    $response->assertNotFound();
});

it('updates a customer address', function () {
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

    /** @var TestCase $this */
    $response = $this->putJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id), $attributes);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $address->id)
            ->where('data.first_name', $attributes['first_name'])
            ->where('data.last_name', $attributes['last_name'])
            ->where('data.phone', [
                'e164' => '+51912345678',
                'international' => '+51 912 345 678',
                'national' => '912 345 678',
                'mobile_dialing' => '912345678',
            ])
            ->where('data.address1', $attributes['address1'])
            ->where('data.address2', $attributes['address2'])
            ->where('data.district', $attributes['district'])
            ->where('data.city', $attributes['city'])
            ->where('data.state', $attributes['state'])
        );
});

it('sets the address as default when updating a customer address with default is true', function () {
    $customer = Customer::factory()->create();
    $address1 = CustomerAddress::factory()->for($customer)->create(['default' => true]);
    $address2 = CustomerAddress::factory()->for($customer)->create(['default' => false]);
    $attributes = ['default' => true];

    /** @var TestCase $this */
    $response = $this->putJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address2->id), $attributes);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $address2->id)
            ->where('data.default', true)
        );
});

it('keeps the address as default when updating a customer address with default is false but it is the only default address', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create(['default' => true]);
    $attributes = ['default' => false];

    /** @var TestCase $this */
    $response = $this->putJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id), $attributes);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $address->id)
            ->where('data.default', true)
        );
});

it('throws a validation error when updating a customer address with invalid attributes', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();
    $attributes = [
        'first_name' => '',
        'last_name' => '',
        'phone' => 'invalid-phone',
        'address1' => '',
        'district' => '',
        'city' => '',
        'state' => '',
    ];

    /** @var TestCase $this */
    $response = $this->putJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id), $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'phone', 'address1', 'district', 'city', 'state']);
});

it('throws a not found error when updating a customer address that does not belong to the customer', function () {
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

    /** @var TestCase $this */
    $response = $this->putJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id), $attributes);

    $response->assertNotFound();
});

it('throws a not found error when updating a customer address for a non-existing customer', function () {
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

    /** @var TestCase $this */
    $response = $this->putJson(sprintf('/api/c/customers/999/addresses/%s', $address->id), $attributes);

    $response->assertNotFound();
});

it('throws a not found error when updating a non-existing customer address for a non-existing customer', function () {
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

    /** @var TestCase $this */
    $response = $this->putJson('/api/c/customers/999/addresses/999', $attributes);

    $response->assertNotFound();
});

it('throws a not found error when updating a non-existing customer address for an existing customer', function () {
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

    /** @var TestCase $this */
    $response = $this->putJson(sprintf('/api/c/customers/%s/addresses/999', $customer->id), $attributes);

    $response->assertNotFound();
});

it('deletes a customer address', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    /** @var TestCase $this */
    $response = $this->deleteJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id));

    $response->assertNoContent();
});

it('throws a not found error when deleting a customer address that does not belong to the customer', function () {
    $customer = Customer::factory()->create();
    $otherCustomer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($otherCustomer)->create();

    /** @var TestCase $this */
    $response = $this->deleteJson(sprintf('/api/c/customers/%s/addresses/%s', $customer->id, $address->id));

    $response->assertNotFound();
});

it('throws a not found error when deleting a customer address for a non-existing customer', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    /** @var TestCase $this */
    $response = $this->deleteJson(sprintf('/api/c/customers/999/addresses/%s', $address->id));

    $response->assertNotFound();
});

it('throws a not found error when deleting a non-existing customer address for an existing customer', function () {
    $customer = Customer::factory()->create();
    $address = CustomerAddress::factory()->for($customer)->create();

    /** @var TestCase $this */
    $response = $this->deleteJson(sprintf('/api/c/customers/%s/addresses/999', $customer->id));

    $response->assertNotFound();
});

it('throws a not found error when deleting a non-existing customer address for a non-existing customer', function () {
    /** @var TestCase $this */
    $response = $this->deleteJson('/api/c/customers/999/addresses/999');

    $response->assertNotFound();
});
