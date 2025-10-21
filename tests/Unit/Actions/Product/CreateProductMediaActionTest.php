<?php

declare(strict_types=1);

use App\Actions\Product\CreateProductMediaAction;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('creates product media for a product', function () {
    Storage::fake('s3');

    $product = Product::factory()->create();

    $attributes = [
        ['file' => UploadedFile::fake()->image('media1.jpg'), 'rank' => 1],
        ['file' => UploadedFile::fake()->image('media2.jpg'), 'rank' => 2],
    ];

    /** @var CreateProductMediaAction $action */
    $action = app(CreateProductMediaAction::class);
    $mediaCollection = $action->handle($product, $attributes);

    expect($mediaCollection)->toHaveCount(2);
    expect($mediaCollection)->each->toBeInstanceOf(ProductMedia::class);

    $mediaCollection->each(function (ProductMedia $media, $key) use ($product, $attributes) {
        expect($media->product_id)->toBe($product->id);
        expect($media->url)->not->toBeEmpty();
        expect($media->rank)->toBe($attributes[$key]['rank']);
    });
});

it('handles empty media attributes', function () {
    $product = Product::factory()->create();

    $attributes = [];

    /** @var CreateProductMediaAction $action */
    $action = app(CreateProductMediaAction::class);
    $mediaCollection = $action->handle($product, $attributes);

    expect($mediaCollection)->toHaveCount(0);
});

it('throws an exception for invalid file types', function () {
    $product = Product::factory()->create();

    $attributes = [
        ['file' => 'not-a-file', 'rank' => 1],
    ];

    /** @var CreateProductMediaAction $action */
    $action = app(CreateProductMediaAction::class);

    $action->handle($product, $attributes);
})->throws(InvalidArgumentException::class, 'The file must be an instance of UploadedFile.');
