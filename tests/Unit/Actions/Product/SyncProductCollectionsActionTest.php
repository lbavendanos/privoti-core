<?php

declare(strict_types=1);

use App\Actions\Product\SyncProductCollectionsAction;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

it('syncs collections for a product', function (): void {
    /** @var Product $product */
    $product = Product::factory()->create();
    /** @var EloquentCollection<int,Collection> $initialCollections */
    $initialCollections = Collection::factory()->count(2)->create();

    // Attach initial collections
    $product->collections()->attach($initialCollections->pluck('id')->toArray());

    /** @var EloquentCollection<int,Collection> $newCollections */
    $newCollections = Collection::factory()->count(3)->create();

    /** @var list<int> $newCollectionIds */
    $newCollectionIds = $newCollections->pluck('id')->toArray();

    /** @var SyncProductCollectionsAction $action */
    $action = app(SyncProductCollectionsAction::class);

    $action->handle($product, $newCollectionIds);

    expect($product->collections()->count())->toBe(3);

    foreach ($newCollections as $collection) {
        expect($product->collections()->where('id', $collection->id)->exists())->toBeTrue();
    }

    foreach ($initialCollections as $collection) {
        expect($product->collections()->where('id', $collection->id)->exists())->toBeFalse();
    }
});

it('detaches all collections when given an empty array', function (): void {
    /** @var Product $product */
    $product = Product::factory()->create();
    /** @var EloquentCollection<int,Collection> $initialCollections */
    $initialCollections = Collection::factory()->count(2)->create();

    // Attach initial collections
    $product->collections()->attach($initialCollections->pluck('id')->toArray());

    /** @var SyncProductCollectionsAction $action */
    $action = app(SyncProductCollectionsAction::class);

    $action->handle($product, []);

    expect($product->collections()->count())->toBe(0);
});
