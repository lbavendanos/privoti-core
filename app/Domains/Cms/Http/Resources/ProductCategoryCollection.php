<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class ProductCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn ($category): ProductCategoryResource => new ProductCategoryResource($category)),
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function toResponse($request)
    {
        $response = parent::toResponse($request);
        $data = $response->getData(true);

        unset($data['links']);
        unset($data['meta']['path']);
        unset($data['meta']['links']);

        $response->setData($data);

        return $response;
    }
}
