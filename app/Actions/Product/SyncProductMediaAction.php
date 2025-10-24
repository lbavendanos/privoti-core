<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class SyncProductMediaAction
{
    public function __construct(
        private CreateProductMediaAction $createProductMediaAction,
        private UpdateProductMediaAction $updateProductMediaAction
    ) {
        //
    }

    /**
     * Sync product media.
     *
     * @param  list<array<string,mixed>>  $attributes
     * @return array{attached: list<int>, detached: list<int>, updated: list<int>}
     */
    public function handle(Product $product, array $attributes): array
    {
        /** @phpstan-ignore-next-line */
        return DB::transaction(function () use ($product, $attributes): array {
            $changes = [
                'attached' => [], 'detached' => [], 'updated' => [],
            ];

            if (blank($attributes)) {
                $changes['detached'] = $product->media()->pluck('id')->toArray();
                $product->media()->delete();
            } else {
                /** @var Collection<int, array{'id'?: int}> $mediaAttributes */
                $mediaAttributes = collect($attributes);
                $existingMedia = $product->media()->pluck('id')->toArray();

                $mediaToKeep = $mediaAttributes->filter(fn ($media): bool => isset($media['id']))->pluck('id')->toArray();
                $mediaToDelete = array_diff($existingMedia, $mediaToKeep);

                if (filled($mediaToDelete)) {
                    $product->media()->whereIn('id', $mediaToDelete)->delete();
                    $changes['detached'] = array_values($mediaToDelete);
                }

                /** @var list<array<string,mixed>> $mediaToUpdate */
                $mediaToUpdate = $mediaAttributes->filter(fn ($media): bool => isset($media['id']))->toArray();
                if (filled($mediaToUpdate)) {
                    $mediaUpdated = $this->updateProductMediaAction->handle($product, $mediaToUpdate);
                    $changes['updated'] = $mediaUpdated->pluck('id')->toArray();
                }

                /** @var list<array<string,mixed>> $mediaToCreate */
                $mediaToCreate = $mediaAttributes->filter(fn ($media): bool => ! isset($media['id']))->toArray();
                if (filled($mediaToCreate)) {
                    $mediaCreated = $this->createProductMediaAction->handle($product, $mediaToCreate);
                    $changes['attached'] = $mediaCreated->pluck('id')->toArray();
                }
            }

            return $changes;
        });
    }
}
