<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

beforeEach(function () {
    $user = User::factory()->create();
    /** @var TestCase $this */
    $this->actingAs($user, 'cms');
});

it('returns a customer collection', function () {
    Customer::factory()->count(10)->create();

    /** @var TestCase $this */
    $response = $this->getJson('/api/c/customers');

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 10)
            ->has('meta')
        );
});

it('creates a customer', function () {
    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'm@example.com',
        'dob' => fake()->date(),
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/api/c/customers', $attributes);

    $response
        ->assertCreated()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.first_name', $attributes['first_name'])
            ->where('data.last_name', $attributes['last_name'])
            ->where('data.email', $attributes['email'])
            ->where('data.dob', $attributes['dob'])
            ->where('data.account', Customer::ACCOUNT_GUEST)
        );
});

it('throws a validation error when creating a customer with invalid attributes', function () {
    $attributes = [
        'first_name' => '',
        'last_name' => '',
        'email' => 'invalid-email',
        'dob' => 'invalid-date',
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/api/c/customers', $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'dob']);
});

it('throws a validation error when creating a customer with duplicated email', function () {
    Customer::factory()->create([
        'email' => 'm@example.com',
    ]);

    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'm@example.com',
    ];

    /** @var TestCase $this */
    $response = $this->postJson('/api/c/customers', $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('shows a customer', function () {
    $customer = Customer::factory()->create();

    /** @var TestCase $this */
    $response = $this->getJson("/api/c/customers/{$customer->id}");

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $customer->id)
            ->where('data.email', $customer->email)
            ->etc()
        );
});

it('returns 404 when showing a non-existing customer', function () {
    /** @var TestCase $this */
    $response = $this->getJson('/api/c/customers/999999');

    $response->assertNotFound();
});

it('updates a customer', function () {
    $customer = Customer::factory()->create();

    $attributes = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'm@example.com',
        'dob' => fake()->date(),
    ];

    /** @var TestCase $this */
    $response = $this->putJson("/api/c/customers/{$customer->id}", $attributes);

    $response
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.id', $customer->id)
            ->where('data.first_name', $attributes['first_name'])
            ->where('data.last_name', $attributes['last_name'])
            ->where('data.email', $attributes['email'])
            ->where('data.dob', $attributes['dob'])
            ->etc()
        );
});

it('throws a validation error when updating a customer with invalid attributes', function () {
    $customer = Customer::factory()->create();

    $attributes = [
        'first_name' => '',
        'last_name' => '',
        'email' => 'invalid-email',
        'dob' => 'invalid-date',
    ];

    /** @var TestCase $this */
    $response = $this->putJson("/api/c/customers/{$customer->id}", $attributes);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'dob']);
});

it('throws a validation error when updating a customer with duplicated email', function () {
    Customer::factory()->create([
        'email' => 'm@example.com',
    ]);

    $customer = Customer::factory()->create();

    /** @var TestCase $this */
    $response = $this->putJson("/api/c/customers/{$customer->id}", [
        'email' => 'm@example.com',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('deletes a customer', function () {
    $customer = Customer::factory()->create();

    /** @var TestCase $this */
    $response = $this->deleteJson("/api/c/customers/{$customer->id}");

    $response->assertNoContent();

    expect(Customer::query()->find($customer->id))->toBeNull();
});

it('returns 404 when deleting a non-existing customer', function () {
    /** @var TestCase $this */
    $response = $this->deleteJson('/api/c/customers/999999');

    $response->assertNotFound();
});

it('bulk deletes customers', function () {
    $customers = Customer::factory()->count(3)->create();
    $ids = $customers->pluck('id')->all();

    /** @var TestCase $this */
    $response = $this->deleteJson('/api/c/customers', [
        'ids' => $ids,
    ]);

    $response->assertNoContent();

    foreach ($ids as $id) {
        expect(Customer::query()->find($id))->toBeNull();
    }
});
