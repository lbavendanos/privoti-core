<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class SyncProductOptionsAction
{
    public function __construct(
        private CreateProductOptionsAction $createProductOptionsAction,
        private UpdateProductOptionsAction $updateProductOptionsAction,
    ) {
        //
    }

    /**
     * Sync product options.
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
                $changes['detached'] = $product->options()->pluck('id')->toArray();
                $product->values()->delete();
                $product->options()->delete();
            } else {
                /** @var Collection<int, array{'id'?: int}> $optionAttributes */
                $optionAttributes = collect($attributes);
                $existingOptions = $product->options()->pluck('id')->toArray();

                $optionsToKeep = $optionAttributes->filter(fn ($option): bool => isset($option['id']))->pluck('id')->toArray();
                $optionsToDelete = array_diff($existingOptions, $optionsToKeep);

                if (filled($optionsToDelete)) {
                    $product->values()->whereIn('option_id', $optionsToDelete)->delete();
                    $product->options()->whereIn('id', $optionsToDelete)->delete();
                    $changes['detached'] = array_values($optionsToDelete);
                }

                /** @var list<array<string,mixed>> $optionsToUpdate */
                $optionsToUpdate = $optionAttributes->filter(fn ($option): bool => isset($option['id']))->toArray();
                if (filled($optionsToUpdate)) {
                    $optionsUpdated = $this->updateProductOptionsAction->handle($product, $optionsToUpdate);
                    $changes['updated'] = $optionsUpdated->pluck('id')->toArray();
                }

                /** @var list<array<string,mixed>> $optionsToCreate */
                $optionsToCreate = $optionAttributes->filter(fn ($option): bool => ! isset($option['id']))->toArray();
                if (filled($optionsToCreate)) {
                    $optionsCreated = $this->createProductOptionsAction->handle($product, $optionsToCreate);
                    $changes['attached'] = $optionsCreated->pluck('id')->toArray();
                }
            }

            return $changes;
        });
    }
}
