<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\ProductOption;
use Illuminate\Support\Facades\DB;

final readonly class SyncProductOptionValuesAction
{
    public function __construct(
        private CreateProductOptionValuesAction $createProductOptionValuesAction,
    ) {
        //
    }

    /**
     * Sync product option values.
     *
     * @param  list<string>  $attributes
     * @return array{attached: list<int>, detached: list<int>, updated: list<int>}
     */
    public function handle(ProductOption $option, array $attributes): array
    {
        /** @phpstan-ignore-next-line */
        return DB::transaction(function () use ($option, $attributes): array {
            $changes = [
                'attached' => [], 'detached' => [], 'updated' => [],
            ];

            if (blank($attributes)) {
                $changes['detached'] = $option->values()->pluck('id')->toArray();
                $option->values()->delete();
            } else {
                $existingValues = $option->values()->pluck('value')->toArray();
                $valuesToDelete = array_diff($existingValues, $attributes);

                if (filled($valuesToDelete)) {
                    $changes['detached'] = $option->values()->whereIn('value', $valuesToDelete)->pluck('id')->toArray();
                    $option->values()->whereIn('value', $valuesToDelete)->delete();
                }

                /** @var list<string> $valuesToCreate */
                $valuesToCreate = array_diff($attributes, $existingValues);
                if (filled($valuesToCreate)) {
                    $valuesCreated = $this->createProductOptionValuesAction->handle($option, $valuesToCreate);
                    $changes['attached'] = $valuesCreated->pluck('id')->toArray();
                }
            }

            return $changes;
        });
    }
}
