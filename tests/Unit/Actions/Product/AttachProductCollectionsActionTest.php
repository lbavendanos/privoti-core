<?php

declare(strict_types=1);

use App\Actions\Product\AttachProductCollectionsAction;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

it('attaches collections to a product', function (): void {
    /** @var Product $product */
    $product = Product::factory()->create();
    /** @var EloquentCollection<int,Collection> $collections */
    $collections = Collection::factory()->count(3)->create();

    /** @var list<int> $collectionIds */
    $collectionIds = $collections->pluck('id')->toArray();

    /** @var AttachProductCollectionsAction $action */
    $action = app(AttachProductCollectionsAction::class);

    $action->handle($product, $collectionIds);

    expect($product->collections()->count())->toBe(3);

    foreach ($collections as $collection) {
        expect($product->collections()->where('id', $collection->id)->exists())->toBeTrue();
    }
});
