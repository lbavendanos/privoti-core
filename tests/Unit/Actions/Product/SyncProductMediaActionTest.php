<?php

declare(strict_types=1);

use App\Actions\Product\SyncProductMediaAction;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('syncs product media for a product', function () {
    Storage::fake('s3');

    $product = Product::factory()->create();
    $existingMedia1 = ProductMedia::factory()->for($product)->create(['rank' => 1]);
    $existingMedia2 = ProductMedia::factory()->for($product)->create(['rank' => 2]);

    $attributes = [
        ['id' => $existingMedia1->id, 'rank' => 3],
        ['file' => UploadedFile::fake()->image('new_media.jpg'), 'rank' => 4],
    ];

    /** @var SyncProductMediaAction $action */
    $action = app(SyncProductMediaAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedMedia */
    $syncedMedia = $action->handle($product, $attributes);

    expect($syncedMedia['attached'])->toHaveCount(1);
    expect($syncedMedia['detached'])->toHaveCount(1);
    expect($syncedMedia['detached'])->toContain($existingMedia2->id);
    expect($syncedMedia['updated'])->toHaveCount(1);
});

it('handles empty media attributes by detaching all media', function () {
    $product = Product::factory()->create();
    $existingMedia1 = ProductMedia::factory()->for($product)->create(['rank' => 1]);
    $existingMedia2 = ProductMedia::factory()->for($product)->create(['rank' => 2]);

    $attributes = [];

    /** @var SyncProductMediaAction $action */
    $action = app(SyncProductMediaAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedMedia */
    $syncedMedia = $action->handle($product, $attributes);

    expect($syncedMedia['attached'])->toHaveCount(0);
    expect($syncedMedia['detached'])->toHaveCount(2);
    expect($syncedMedia['detached'])->toContain($existingMedia1->id);
    expect($syncedMedia['detached'])->toContain($existingMedia2->id);
    expect($syncedMedia['updated'])->toHaveCount(0);
});

it('handles all new media attributes by attaching all media', function () {
    Storage::fake('s3');

    $product = Product::factory()->create();

    $attributes = [
        ['file' => UploadedFile::fake()->image('new_media1.jpg'), 'rank' => 1],
        ['file' => UploadedFile::fake()->image('new_media2.jpg'), 'rank' => 2],
    ];

    /** @var SyncProductMediaAction $action */
    $action = app(SyncProductMediaAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedMedia */
    $syncedMedia = $action->handle($product, $attributes);

    expect($syncedMedia['attached'])->toHaveCount(2);
    expect($syncedMedia['detached'])->toHaveCount(0);
    expect($syncedMedia['updated'])->toHaveCount(0);
});

it('handles all existing media attributes by updating all media', function () {
    $product = Product::factory()->create();
    $existingMedia1 = ProductMedia::factory()->for($product)->create(['rank' => 1]);
    $existingMedia2 = ProductMedia::factory()->for($product)->create(['rank' => 2]);

    $attributes = [
        ['id' => $existingMedia1->id, 'rank' => 3],
        ['id' => $existingMedia2->id, 'rank' => 4],
    ];

    /** @var SyncProductMediaAction $action */
    $action = app(SyncProductMediaAction::class);
    /** array{attached: list<int>, detached: list<int>, updated: list<int>} $syncedMedia */
    $syncedMedia = $action->handle($product, $attributes);

    expect($syncedMedia['attached'])->toHaveCount(0);
    expect($syncedMedia['detached'])->toHaveCount(0);
    expect($syncedMedia['updated'])->toHaveCount(2);
});
