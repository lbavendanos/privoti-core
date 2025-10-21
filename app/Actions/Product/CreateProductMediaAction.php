<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\Common\StoreFileAction;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CreateProductMediaAction
{
    private const string DIRECTORY = 'products';

    public function __construct(
        private StoreFileAction $storeFileAction
    ) {
        //
    }

    /**
     * Create product media.
     *
     * @param  list<array{file: UploadedFile, rank: int}>  $attributes
     * @return Collection<int,ProductMedia>
     */
    public function handle(Product $product, array $attributes): Collection
    {
        return DB::transaction(function () use ($product, $attributes): Collection {
            /** @var Collection<int,ProductMedia> $collection */
            $collection = collect();

            foreach ($attributes as $key => $attribute) {
                ['file' => $file, 'rank' => $rank] = $attribute;

                $filename = sprintf('%s-%s', $product->handle, ($key + 1));
                $url = $this->storeFileAction->handle($file, self::DIRECTORY, $filename);

                $media = $product->media()->create([
                    'url' => $url,
                    'rank' => $rank,
                ]);

                $collection->push($media);
            }

            return $collection;
        });
    }
}
