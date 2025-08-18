<?php

declare(strict_types=1);

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class ProductTypeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn ($type): \App\Domains\Cms\Http\Resources\ProductTypeResource => new ProductTypeResource($type)),
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
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
