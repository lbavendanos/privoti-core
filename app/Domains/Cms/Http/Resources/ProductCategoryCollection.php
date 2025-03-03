<?php

namespace App\Domains\Cms\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'handle' => $category->handle,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'is_public' => $category->is_public,
                    'rank' => $category->rank,
                    'parent_id' => $category->parent_id,
                    'metadata' => $category->metadata,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            }),
        ];
    }
}
