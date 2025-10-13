<?php

declare(strict_types=1);

use App\Models\Vendor;

it('can create a vendor', function () {
    $vendor = Vendor::factory()->create();

    expect($vendor)->toBeInstanceOf(Vendor::class);
    expect($vendor->id)->toBeGreaterThan(0);
});

it('can have many products', function () {
    $vendor = Vendor::factory()->hasProducts(5)->create();

    expect($vendor->products)->toHaveCount(5);
});
