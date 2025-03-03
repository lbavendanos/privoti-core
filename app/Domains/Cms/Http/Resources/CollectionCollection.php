<?php

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CollectionCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'title' => $collection->title,
                    'handle' => $collection->handle,
                    'description' => $collection->description,
                    'metadata' => $collection->metadata,
                    'created_at' => $collection->created_at,
                    'updated_at' => $collection->updated_at,
                ];
            }),
        ];
    }
}
