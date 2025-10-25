<?php

declare(strict_types=1);

use App\Actions\Product\UpdateProductsAction;
use App\Models\Product;

it('updates multiple products', function () {
    $products = Product::factory()->count(3)->create();

    /** @var list<array<string, mixed>> $attributes */
    $attributes = $products->map(function (Product $product, int $index) {
        return [
            'id' => $product->id,
            'title' => 'Updated Product '.($index + 1),
            'subtitle' => 'This is updated product '.($index + 1),
            'description' => 'Detailed description of updated product '.($index + 1).'.',
            'tags' => ['updated', 'product', (string) ($index + 1)],
            'metadata' => ['updated' => true, 'version' => $index + 1],
        ];
    })->toArray();

    /** @var UpdateProductsAction $action */
    $action = app(UpdateProductsAction::class);
    $updatedProducts = $action->handle($attributes);

    expect($updatedProducts)->toHaveCount(3);

    foreach ($updatedProducts as $index => $updatedProduct) {
        $expectedAttributes = $attributes[$index];

        expect($updatedProduct)->toBeInstanceOf(Product::class)
            ->and($updatedProduct->title)->toBe($expectedAttributes['title'])
            ->and($updatedProduct->subtitle)->toBe($expectedAttributes['subtitle'])
            ->and($updatedProduct->description)->toBe($expectedAttributes['description'])
            ->and($updatedProduct->tags)->toBe($expectedAttributes['tags'])
            ->and($updatedProduct->metadata)->toBe($expectedAttributes['metadata']);
    }
});

it('updates multiple product statuses', function () {
    $products = Product::factory()->count(3)->create(['status' => 'draft']);

    /** @var list<array<string, mixed>> $attributes */
    $attributes = $products->map(function (Product $product) {
        return [
            'id' => $product->id,
            'status' => 'active',
        ];
    })->toArray();

    /** @var UpdateProductsAction $action */
    $action = app(UpdateProductsAction::class);
    $updatedProducts = $action->handle($attributes);

    expect($updatedProducts)->toHaveCount(3);

    foreach ($updatedProducts as $updatedProduct) {
        expect($updatedProduct)->toBeInstanceOf(Product::class)
            ->and($updatedProduct->status)->toBe('active');
    }
});
