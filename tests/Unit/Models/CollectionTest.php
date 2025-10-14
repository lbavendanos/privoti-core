<?php

declare(strict_types=1);

use App\Models\Collection;

it('can create a collection', function () {
    $collection = Collection::factory()->create();

    expect($collection)->toBeInstanceOf(Collection::class);
    expect($collection->id)->toBeGreaterThan(0);
});

it('can have many products', function () {
    $collection = Collection::factory()->hasProducts(5)->create();

    expect($collection->products)->toHaveCount(5);
});
