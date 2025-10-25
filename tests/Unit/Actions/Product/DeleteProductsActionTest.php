<?php

declare(strict_types=1);

use App\Actions\Product\DeleteProductsAction;
use App\Models\Product;

it('deletes multiple products', function () {
    $products = Product::factory()->count(3)->create();
    /** @var list<int> $ids */
    $ids = $products->pluck('id')->all();

    app(DeleteProductsAction::class)->handle($ids);

    expect(Product::query()->whereIn('id', $ids)->count())->toBe(0);
});
