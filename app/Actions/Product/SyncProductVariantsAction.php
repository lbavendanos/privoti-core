<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class SyncProductVariantsAction
{
    public function __construct(
        private CreateProductVariantsAction $createProductVariantsAction,
        private UpdateProductVariantsAction $updateProductVariantsAction,
    ) {
        //
    }

    /**
     * Sync product variants.
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
                $changes['detached'] = $product->variants()->pluck('id')->toArray();
                $product->variants()->delete();
            } else {
                /** @var Collection<int, array{'id'?: int}> $variantAttributes */
                $variantAttributes = collect($attributes);
                $existingVariants = $product->variants()->pluck('id')->toArray();

                $variantsToKeep = $variantAttributes->filter(fn ($variant): bool => isset($variant['id']))->pluck('id')->toArray();
                $variantsToDelete = array_diff($existingVariants, $variantsToKeep);

                if (filled($variantsToDelete)) {
                    $product->variants()->whereIn('id', $variantsToDelete)->delete();
                    $changes['detached'] = array_values($variantsToDelete);
                }

                /** @var list<array<string,mixed>> $variantsToUpdate */
                $variantsToUpdate = $variantAttributes->filter(fn ($variant): bool => isset($variant['id']))->toArray();
                if (filled($variantsToUpdate)) {
                    $variantsUpdated = $this->updateProductVariantsAction->handle($product, $variantsToUpdate);
                    $changes['updated'] = $variantsUpdated->pluck('id')->toArray();
                }

                /** @var list<array<string,mixed>> $variantsToCreate */
                $variantsToCreate = $variantAttributes->filter(fn ($variant): bool => ! isset($variant['id']))->toArray();
                if (filled($variantsToCreate)) {
                    $variantsCreated = $this->createProductVariantsAction->handle($product, $variantsToCreate);
                    $changes['attached'] = $variantsCreated->pluck('id')->toArray();
                }
            }

            return $changes;
        });
    }
}
