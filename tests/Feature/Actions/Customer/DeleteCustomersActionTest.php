<?php

declare(strict_types=1);

use App\Actions\Customer\DeleteCustomersAction;
use App\Models\Customer;

it('deletes multiple customers', function () {
    $customers = Customer::factory()->count(3)->create();
    /** @var list<int> $ids */
    $ids = $customers->pluck('id')->all();

    app(DeleteCustomersAction::class)->handle($ids);

    expect(Customer::query()->whereIn('id', $ids)->count())->toBe(0);
});
